<?php

namespace OxCom\MagentoCurrencyServices\Model\Currency\Import;

/**
 * Calculates currency pairs from rates relative to one base currency.
 */
class CrossRates extends AbstractDto
{
    /**
     * @var string
     */
    protected $base;

    /**
     * @var array
     */
    protected $rates = [];

    /**
     * @param array|object|null $object
     * @param string $base
     */
    public function __construct($object = null, $base = '')
    {
        parent::__construct($object);

        $this->base = $base;
        $this->rates = $this->val($object, 'rates', []);
    }

    /**
     * @return string
     */
    public function getBase()
    {
        return $this->base;
    }

    /**
     * @param string|null $currencyFrom
     * @param string|null $currencyTo
     *
     * @return float|null
     */
    public function getRates($currencyFrom = null, $currencyTo = null)
    {
        if (empty($currencyFrom) || empty($currencyTo)) {
            return null;
        }

        if ($currencyFrom === $currencyTo) {
            return 1;
        }

        if ($currencyFrom === $this->getBase()) {
            $rate = $this->val($this->rates, $currencyTo);
        } elseif ($currencyTo === $this->getBase()) {
            $rate = $this->val($this->rates, $currencyFrom);
            $rate = empty($rate) ? null : \bcdiv(1, $rate, AbstractSource::SCALE);
        } else {
            $rateFrom = $this->val($this->rates, $currencyFrom);
            $rateTo = $this->val($this->rates, $currencyTo);
            $rate = (empty($rateFrom) || empty($rateTo))
                ? null
                : \bcdiv($rateTo, $rateFrom, AbstractSource::SCALE);
        }

        return $rate === null ? null : (double)\bcdiv($rate, '1', AbstractSource::SCALE);
    }
}
