<?php

namespace OxCom\MagentoCurrencyServices\Model\Currency\Import;

/**
 * Class Yahoo
 *
 * @package OxCom\MagentoCurrencyServices\Model\Currency\Import
 */
class Yahoo extends AbstractSource
{
    const SOURCE_NAME = 'yahoo';
    // @codingStandardsIgnoreStart
    const SOURCE_LINK = 'https://query.yahooapis.com/v1/public/yql?q=select%20*%20from%20yahoo.finance.xchange%20where%20pair%20in%20(%22{{CURRENCY_FROM}}{{CURRENCY_TO}}%22)&format=json&env=store%3A%2F%2Fdatatables.org%2Falltableswithkeys&callback=';
    // @codingStandardsIgnoreStop

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
            $data = json_decode($response, true);

            if (empty($data['query']['results']['rate']['Rate'])) {
                throw new \Exception();
            }

            $rate = (double)$data['query']['results']['rate']['Rate'];
        } catch (\Exception $e) {
            $this->_messages[] = __("We can't retrieve a rate from %1.", $url);
        }

        return $rate;
    }
}
