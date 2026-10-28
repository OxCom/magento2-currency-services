<?php

namespace OxCom\MagentoCurrencyServices\Model\Currency\Import\Google;

use OxCom\MagentoCurrencyServices\Model\Currency\Import\AbstractDto;
use OxCom\MagentoCurrencyServices\Model\Currency\Import\AbstractSource;

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
    protected $rate = null;

    /**
     * User constructor.
     *
     * @param array|object|null $object
     */
    public function __construct($object = null)
    {
        parent::__construct($object);

        // $object should contain the result of the quote-page match.
        $rate = \str_replace(',', '', (string)$this->val($object, 1, ''));

        if (\is_numeric($rate) && (float)$rate > 0) {
            $this->rate = \bcdiv($rate, '1', AbstractSource::SCALE);
        }
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
