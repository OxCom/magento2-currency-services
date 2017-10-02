<?php

namespace OxCom\CurrencyServices\Model\Currency\Import\Fixer;

use OxCom\CurrencyServices\Model\Currency\Import\AbstractDto;

/**
 * Class Rates
 *
 * @package OxCom\CurrencyServices\Model\Currency\Import\Fixer
 */
class Rates extends AbstractDto
{
    /**
     * @var string
     */
    protected $base;

    /**
     * @var \DateTimeImmutable
     */
    protected $date;

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

        $this->base = $this->val($object, 'base', '');

        $date = $this->val($object, 'date');
        if (!empty($date)) {
            $this->date = \DateTimeImmutable::createFromFormat(static::DATE_FORMAT, $date);
        }

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
     * @return \DateTimeImmutable
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param string $currency
     *
     * @return double
     */
    public function getRates($currency = null)
    {
        if (empty($currency)) {
            return null;
        }

        if ($currency === $this->getBase()) {
            return 1;
        }

        return $this->val($this->rates, $currency);
    }
}
