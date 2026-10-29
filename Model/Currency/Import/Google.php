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
    public const SOURCE_NAME = 'google';
    public const SOURCE_LINK = 'https://www.google.com/finance/quote/{{CURRENCY_FROM}}-{{CURRENCY_TO}}?hl=en&gl=US';

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

        // No remote request is needed when the currencies are identical.
        if ($currencyFrom === $currencyTo) {
            return 1;
        }

        $rate = null;
        $url  = strtr(static::SOURCE_LINK, [
            '{{CURRENCY_FROM}}' => $currencyFrom,
            '{{CURRENCY_TO}}'   => $currencyTo,
        ]);

        try {
            $response = $this->request($url);
            $matches = [];
            $pair = \preg_quote($currencyFrom . ' / ' . $currencyTo, '/');
            \preg_match(
                '/title="' . $pair . '".{0,10000}?jsname="Pdsbrc"[^>]*>\s*<span>([0-9,.]+)<\/span>/si',
                $response,
                $matches
            );

            $rates = new Rates($matches);
            $rate  = $rates->getRates($currencyFrom, $currencyTo);

            if (empty($rate)) {
                throw new \Exception();
            }
        } catch (\Exception $e) {
            $this->addRetrieveError($url, $this->getLastRequestError());
        }

        return (float)$rate;
    }
}
