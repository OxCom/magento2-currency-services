<?php

namespace OxCom\MagentoCurrencyServices\Model\Currency\Import;

use OxCom\MagentoCurrencyServices\Model\Currency\Import\BankOfCanada\Rates;

/**
 * Imports the latest business-day exchange rates from the Bank of Canada.
 */
class BankOfCanada extends AbstractSource
{
    public const SOURCE_NAME = 'bank_of_canada';
    public const SOURCE_LINK = 'https://www.bankofcanada.ca/valet/observations/group/FX_RATES_DAILY/json?recent=1';

    /**
     * @param string $currencyFrom
     * @param string $currencyTo
     *
     * @return float
     *
     * @codingStandardsIgnoreStart
     */
    protected function _convert($currencyFrom, $currencyTo)
    {
        // @codingStandardsIgnoreStop
        $this->doRequestDelay();

        if ($currencyFrom === $currencyTo) {
            return 1;
        }

        $rate = null;
        $url = static::SOURCE_LINK;

        try {
            $payload = $this->processPayload($this->request($url), []);
            $rates = new Rates(['rates' => $this->extractLatestRates($payload)]);
            $rate = $rates->getRates($currencyFrom, $currencyTo);

            if (empty($rate)) {
                throw new \Exception();
            }
        } catch (\Exception $e) {
            $this->addRetrieveError($url, $this->getLastRequestError());
        }

        return (double)$rate;
    }

    /**
     * Normalize CAD-per-currency observations to currency-per-CAD rates.
     *
     * @param array $payload
     *
     * @return array
     */
    private function extractLatestRates(array $payload)
    {
        $observations = isset($payload['observations']) && \is_array($payload['observations'])
            ? $payload['observations']
            : [];
        $latestDate = '';

        foreach ($observations as $observation) {
            if (!empty($observation['d']) && $observation['d'] > $latestDate) {
                $latestDate = $observation['d'];
            }
        }

        $rates = [];
        foreach ($observations as $observation) {
            if (empty($latestDate) || empty($observation['d']) || $observation['d'] !== $latestDate) {
                continue;
            }

            foreach ($observation as $series => $value) {
                if (!\preg_match('/^FX([A-Z]{3})CAD$/', $series, $matches)) {
                    continue;
                }

                $cadPerCurrency = isset($value['v']) ? $value['v'] : null;
                if (!\is_numeric($cadPerCurrency) || (float)$cadPerCurrency <= 0) {
                    continue;
                }

                $rates[$matches[1]] = \bcdiv(1, $cadPerCurrency, AbstractSource::SCALE * 2);
            }
        }

        return $rates;
    }
}
