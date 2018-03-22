<?php

namespace OxCom\MagentoCurrencyServices\Model\Currency\Import\Google;

use OxCom\MagentoCurrencyServices\Model\Currency\Import\AbstractDto;

/**
 * Class Rates
 *
 * @package OxCom\MagentoCurrencyServices\Model\Currency\Import\Google
 */
class Rates extends AbstractDto
{
    /**
     * @var double
     */
    protected $rate;

    /**
     * User constructor.
     *
     * @param array|object|null $object
     * @param int               $value Requested value
     */
    public function __construct($object = null, $value = 1)
    {
        parent::__construct($object);

        // $object should be result of match
        $match = $this->val($object, 1, []);
        $rate  = $this->val($match, 1, 0);

        $rate       = str_replace(',', '.', $rate);
        $rate       = (double)($rate);
        $this->rate = $rate / ($value < 0 ? 1 : $value);
    }

    /**
     * @param string $currencyFrom
     * @param string $currencyTo
     *
     * @return double
     */
    public function getRates($currencyFrom = '', $currencyTo = '')
    {
        if ($currencyFrom === $currencyTo) {
            return 1;
        }

        return $this->rate;
    }
}
