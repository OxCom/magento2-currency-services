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
    const SOURCE_NAME = 'fixer';
    const SOURCE_LINK = 'http://api.fixer.io/latest?access_key={{TOKEN}}}&base={{FROM}}&symbols={{TO}}';

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

        $rate = null;
        $url  = strtr(static::SOURCE_LINK, [
            '{{TOKEN}}' => $this->getAccessToken(),
            '{{FROM}}'  => $currencyFrom,
            '{{TO}}'    => $currencyTo,
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
