<?php

namespace OxCom\MagentoCurrencyServices\Model\Currency\Import;

use OxCom\MagentoCurrencyServices\Model\Currency\Import\Fixer\Rates;

/**
 * Class Fixer
 *
 * @package OxCom\MagentoCurrencyServices\Model\Currency\Import
 */
class Fixer extends AbstractSource
{
    const SOURCE_NAME      = 'fixer';
    const SOURCE_LINK      = 'http://data.fixer.io/api/latest?access_key={{TOKEN}}&base={{FROM}}&symbols={{TO}}';
    const SOURCE_LINK_FREE = 'http://data.fixer.io/api/latest?access_key={{TOKEN}}';

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
        $markup = $this->getCurrencyMarkup();
        $url  = strtr(static::SOURCE_LINK, [
            '{{TOKEN}}' => $this->getAccessToken(),
            '{{FROM}}'  => $currencyFrom,
            '{{TO}}'    => $currencyTo,
        ]);

        try {
            // We are not able to detect typ of token (free, paid), so we have to do 2 API calls:
            // 1. Request as paid token
            // 2. Request as free token
            $response = $this->request($url);
            $data     = json_decode($response);
            $rates    = new Rates($data);

            if (!empty($data) && empty($data->success)) {
                // example: {"success":false,"error":{"code":105,"type":"base_currency_access_restricted"}}
                $url  = strtr(static::SOURCE_LINK_FREE, [
                    '{{TOKEN}}' => $this->getAccessToken(),
                ]);

                $response = $this->request($url);
                $data     = json_decode($response);
                $rates    = new Rates($data);

                switch (true) {
                    case ($currencyFrom === $rates->getBase()):
                        // this is general case
                        $rate = $rates->getRates($currencyTo);
                        break;

                    case ($currencyTo === $rates->getBase()):
                        $rate = $rates->getRates($currencyFrom);

                        if (empty($rate)) {
                            throw new \Exception();
                        }

                        $rate = 1 / $rate;
                        break;

                    default:
                        // small trick: we can convert from {FROM} to {TO} with intermediate currency
                        // 1 EUR = a * 1 {FROM} and 1 EUR = b * 1 {TO}
                        // so 1 {FROM} = b * 1 {TO} / a
                        $baseFromRate = $rates->getRates($currencyFrom);
                        $baseToRate   = $rates->getRates($currencyTo);

                        if (empty($baseFromRate) || $baseToRate) {
                            throw new \Exception();
                        }

                        $rate = ($baseToRate / $baseFromRate);
                }
            } else {
                $rate = $rates->getRates($currencyTo);
            }

            if (empty($rate)) {
                throw new \Exception();
            }

            $rate = (double)$rate;
        } catch (\Exception $e) {
            $this->_messages[] = __("We can't retrieve a rate from %1.", $url);
        }

        return $rate * $markup;
    }
}
