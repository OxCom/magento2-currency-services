<?php

namespace OxCom\MagentoCurrencyServices\Model\Currency\Import;

use OxCom\MagentoCurrencyServices\Model\Currency\Import\Google\Rates;

/**
 * Class Google
 *
 * @package OxCom\MagentoCurrencyServices\Model\Currency\Import
 */
class Google extends AbstractSource
{
    const SOURCE_NAME = 'google';
    const SOURCE_LINK = 'https://www.google.com/search?safe=off&q={{VALUE}}+{{CURRENCY_FROM}}+{{CURRENCY_TO}}&hl=en-EN';

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

        $zero = rand(1, 5);
        $value = pow(10, $zero);

        $rate = null;
        $markup = $this->getCurrencyMarkup();
        $url  = strtr(static::SOURCE_LINK, [
            '{{CURRENCY_FROM}}' => $currencyFrom,
            '{{CURRENCY_TO}}'   => $currencyTo,
            '{{VALUE}}'         => $value,
        ]);

        try {
            // do we really should do request for the same currency?
            if ($currencyFrom === $currencyTo) {
                return 1;
            }

            $response = $this->request($url);

            $matches = [];
            preg_match_all('/value="([0-9|,|.]+)"[^<]+type="number"/mi', $response, $matches);

            $rates = new Rates($matches, $value);
            $rate  = $rates->getRates($currencyFrom, $currencyTo);

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
