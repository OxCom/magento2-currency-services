<?php

namespace OxCom\MagentoCurrencyServices\Model\Currency\Import\Ecb;

use OxCom\MagentoCurrencyServices\Model\Currency\Import\CrossRates;

/**
 * Euro-based cross rates published by the European Central Bank.
 */
class Rates extends CrossRates
{
    /**
     * @param array|object|null $object
     */
    public function __construct($object = null)
    {
        parent::__construct($object, 'EUR');
    }
}
