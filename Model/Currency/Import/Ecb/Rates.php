<?php

namespace OxCom\CurrencyServices\Model\Currency\Import\Ecb;

use OxCom\CurrencyServices\Model\Currency\Import\AbstractDto;

/**
 * Class Rates
 *
 * @package OxCom\CurrencyServices\Model\Currency\Import\Ecb
 */
class Rates extends AbstractDto
{
    /**
     * @var string
     */
    protected $base = 'EUR';

    /**
     * @var array
     */
    protected $rates = [];

    /**
     * User constructor.
     *
     * @param array|object|null $object
     */
    public function __construct($object = null)
    {
        parent::__construct($object);

        $this->rates = $this->val($object, 'rates', []);
    }

    /**
     * @return object|array
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * @return string
     */
    public function getBase()
    {
        return $this->base;
    }

    /**
     * @param null $currencyFrom
     * @param null $currencyTo
     *
     * @return double
     * @internal param string $currency
     *
     */
    public function getRates($currencyFrom = null, $currencyTo = null)
    {
        if (empty($currencyFrom) || empty($currencyTo)) {
            return null;
        }

        if ($currencyFrom === $currencyTo) {
            return 1;
        }

        switch (true) {
            case $currencyFrom === $this->getBase():
                // from EUR to something
                $rate = $this->val($this->rates, $currencyTo);
                break;

            case $currencyTo === $this->getBase():
                // from something to EUR
                $rate = $this->val($this->rates, $currencyFrom);
                $rate = empty($rate) ? null : (1 / $rate);
                break;

            default:
                /**
                 * From something 1 to something 2:
                 * 1 eur = a currency-from
                 * 1 eur = b currency-to
                 *
                 * a currency-from = b currency-to
                 *
                 * Now we can create proportion:
                 * a currency-from = b currency-to
                 * 1 currency-from = x currency-to
                 *
                 * So,
                 *
                 * x currency-to = 1 currency-from * b currency-to / a currency-from
                 */
                $rateFrom = (double)$this->val($this->rates, $currencyFrom);
                $rateTo   = (double)$this->val($this->rates, $currencyTo);
                $rate     = empty($rateFrom) || empty($rateTo) ? null : ($rateTo / $rateFrom);
        }

        return $rate;
    }
}
