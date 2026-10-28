<?php

namespace OxCom\MagentoCurrencyServices\Model\Currency\Import\BankOfCanada;

use OxCom\MagentoCurrencyServices\Model\Currency\Import\CrossRates;

/**
 * Canadian-dollar-based cross rates published by the Bank of Canada.
 */
class Rates extends CrossRates
{
    /**
     * @param array|object|null $object
     */
    public function __construct($object = null)
    {
        parent::__construct($object, 'CAD');
    }
}
