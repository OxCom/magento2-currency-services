<?php

namespace OxCom\CurrencyServices\Model\Currency\Import;

use OxCom\CurrencyServices\Model\Currency\Import\Fixer\Rates;

/**
 * Class Fixer
 *
 * @package OxCom\CurrencyServices\Model\Currency\Import
 */
class Fixer extends AbstractSource
{
    const SOURCE_NAME = 'fixer';
    const SOURCE_LINK = 'http://api.fixer.io/latest?base={{CURRENCY_FROM}}&symbols={{CURRENCY_TO}}';

    /**
     * Retrieve rate
     *
     * @param   string $currencyFrom
     * @param   string $currencyTo
     *
     * @return  float
     */
    protected function _convert($currencyFrom, $currencyTo)
    {
        $this->doRequestDelay();

        $rate = null;
        $url  = strtr(static::SOURCE_LINK, [
            '{{CURRENCY_FROM}}' => $currencyFrom,
            '{{CURRENCY_TO}}'   => $currencyTo,
        ]);

        try {
            $response = $this->request($url);
            $data     = json_decode($response);
            $rates    = new Rates($data);

            $rate = $rates->getRates($currencyTo);

            if (empty($rate)) {
                throw new \Exception();
            }

            $rate = (double)$rate;
        } catch (\Exception $e) {
            $this->_messages[] = __("We can't retrieve a rate from %1.", $url);
        }

        return $rate;
    }
}
