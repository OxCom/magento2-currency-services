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
    const SOURCE_LINK = 'https://www.google.com/search?safe=off&dcr=0&q=1000+{{CURRENCY_FROM}}+{{CURRENCY_TO}}&hl=en-EN';

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
            preg_match_all('/value="([0-9|,|.]+)"[^<]+type="number"/mi', $response, $matches);

            if (empty($matches[1])) {
                throw new \Exception();
            }

            $rate = str_replace(',', '.', $matches[1][1]);
            $rate = (double)($rate);
            $rate = $rate / 1000;
        } catch (\Exception $e) {
            $this->_messages[] = __("We can't retrieve a rate from %1.", $url);
        }

        return $rate;
    }
}
