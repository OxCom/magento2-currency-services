<?php

namespace OxCom\MagentoCurrencyServices\Model\Currency\Import;

/**
 * Class Google
 *
 * @package OxCom\MagentoCurrencyServices\Model\Currency\Import
 */
class Google extends AbstractSource
{
    const SOURCE_NAME = 'google';
    const SOURCE_LINK = 'https://finance.google.com/finance/converter?a=1&from={{CURRENCY_FROM}}&to={{CURRENCY_TO}}';

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
            '{{CURRENCY_FROM}}' => $currencyFrom,
            '{{CURRENCY_TO}}'   => $currencyTo,
        ]);

        try {
            $response = $this->request($url);

            $matches = [];
            preg_match('/bld>([+-]?([0-9]*[.])?[0-9]+)/', $response, $matches);

            if (empty($matches[1])) {
                throw new \Exception();
            }

            $rate = (double)$matches[1];
        } catch (\Exception $e) {
            $this->_messages[] = __("We can't retrieve a rate from %1.", $url);
        }

        return $rate;
    }
}
