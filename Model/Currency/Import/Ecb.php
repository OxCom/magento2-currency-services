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
    const SOURCE_NAME = 'google';
    const SOURCE_LINK = 'http://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml';

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
            $response = $this->request($url);

            // Default rates are EUR to some currency
            $xml = \simplexml_load_string($response);
            $list = [];

            foreach ($xml->Cube->Cube->Cube as $row) {
                $currency = (string)$row['currency'];

                $list[$currency] = (string)$row['rate'];
            }

            if (empty($list)) {
                throw new \Exception();
            }

            $rates = new Rates(['rates' => $list]);
            $rate  = $rates->getRates($currencyFrom, $currencyTo);
        } catch (\Exception $e) {
            $this->_messages[] = __("We can't retrieve a rate from %1.", $url);
        }

        return (double)$rate;
    }
}
