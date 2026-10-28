<?php

namespace OxCom\MagentoCurrencyServices\Model\Currency\Import;

use OxCom\MagentoCurrencyServices\Model\Currency\Import\Ecb\Rates;

/**
 * Class Ecb
 *
 * @package OxCom\MagentoCurrencyServices\Model\Currency\Import
 */
class Ecb extends AbstractSource
{
    public const SOURCE_NAME = 'ecb';

    /**
     * Daily reference rates feed. HTTPS only: the plain HTTP address answers with a 301 redirect.
     */
    public const SOURCE_LINK = 'https://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml';

    /**
     * ECB Data Portal (SDMX) fallback: last observation of every EUR based daily rate as CSV.
     */
    public const SOURCE_LINK_FALLBACK = 'https://data-api.ecb.europa.eu/service/data/EXR/D..EUR.SP00.A'
        . '?lastNObservations=1&format=csvdata&detail=dataonly';

    /**
     * The fallback feed also returns discontinued series. Observations older than that many days
     * are dropped so that a stale rate can never reach the store.
     */
    public const MAX_RATE_AGE_DAYS = 7;

    /**
     * Retrieve rate
     *
     * @param   string $currencyFrom
     * @param   string $currencyTo
     *
     * @return  float
     *
     * @codingStandardsIgnoreStart
     */
    protected function _convert($currencyFrom, $currencyTo)
    {
        // @codingStandardsIgnoreStop
        $this->doRequestDelay();

        // there should not be any calls
        if ($currencyFrom === $currencyTo) {
            return 1;
        }

        $rate = null;
        $url  = static::SOURCE_LINK;

        try {
            $list = $this->parseDailyXml($this->request($url));

            if (empty($list)) {
                $url  = static::SOURCE_LINK_FALLBACK;
                $list = $this->parseSdmxCsv($this->request($url));
            }

            if (empty($list)) {
                throw new \Exception('Rate list is empty.');
            }

            $rates = new Rates(['rates' => $list]);
            $rate  = $rates->getRates($currencyFrom, $currencyTo);
        } catch (\Exception $e) {
            $this->getLogger()->error('Unable to parse currency rates.', [
                'url' => $url,
                'exception' => $e,
            ]);

            $this->_messages[] = __("We can't retrieve a rate from %1.", $url);
        }

        return (float)$rate;
    }

    /**
     * Parse the eurofxref-daily.xml payload into a `currency => rate` map.
     *
     * @param string $response
     *
     * @return array
     */
    protected function parseDailyXml($response)
    {
        if (empty($response)) {
            return [];
        }

        $useErrors = \libxml_use_internal_errors(true);

        try {
            $xml = \simplexml_load_string($response);
        } finally {
            \libxml_clear_errors();
            \libxml_use_internal_errors($useErrors);
        }

        if ($xml === false || !isset($xml->Cube->Cube->Cube)) {
            return [];
        }

        $list = [];

        foreach ($xml->Cube->Cube->Cube as $row) {
            $currency = (string)$row['currency'];
            $rate     = (string)$row['rate'];

            if ($currency === '' || (float)$rate <= 0) {
                continue;
            }

            $list[$currency] = $rate;
        }

        return $list;
    }

    /**
     * Parse the SDMX CSV fallback payload into a `currency => rate` map.
     *
     * Only EUR denominated observations that are not older than self::MAX_RATE_AGE_DAYS are kept.
     *
     * @param string $response
     *
     * @return array
     */
    protected function parseSdmxCsv($response)
    {
        if (empty($response)) {
            return [];
        }

        $lines  = \preg_split('/\R/', \trim($response));
        $header = \str_getcsv((string)\array_shift($lines));

        if (!\is_array($header)) {
            return [];
        }

        $index = \array_flip($header);
        $keys  = ['CURRENCY', 'CURRENCY_DENOM', 'TIME_PERIOD', 'OBS_VALUE'];

        foreach ($keys as $key) {
            if (!isset($index[$key])) {
                return [];
            }
        }

        $oldest = \strtotime('-' . static::MAX_RATE_AGE_DAYS . ' days');
        $list   = [];

        foreach ($lines as $line) {
            if (\trim($line) === '') {
                continue;
            }

            $row      = \str_getcsv($line);
            $currency = (string)($row[$index['CURRENCY']] ?? '');
            $denom    = (string)($row[$index['CURRENCY_DENOM']] ?? '');
            $period   = (string)($row[$index['TIME_PERIOD']] ?? '');
            $rate     = (string)($row[$index['OBS_VALUE']] ?? '');
            $time     = \strtotime($period);

            if ($currency === '' || $denom !== 'EUR' || (float)$rate <= 0) {
                continue;
            }

            if ($time === false || $time < $oldest) {
                continue;
            }

            $list[$currency] = $rate;
        }

        return $list;
    }
}
