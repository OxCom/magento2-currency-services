<?php

namespace OxCom\MagentoCurrencyServices\Test\Generate;

use OxCom\MagentoCurrencyServices\Model\Currency\Import\Ecb;
use OxCom\MagentoCurrencyServices\Model\Currency\Import\Fixer;
use OxCom\MagentoCurrencyServices\Model\Currency\Import\Google;

/**
 * Class Fixtures
 *
 * @package OxCom\MagentoCurrencyServices\Test\Generate
 */
class Fixtures
{
    /**
     * @param $source
     *
     * @return string
     */
    public static function get($source)
    {
        $rsp = '';
        switch ($source) {
            case Ecb::class:
                $rsp = \file_get_contents(__DIR__. '/Fixture/ecb.xml');
                break;

            case Fixer::class:
                $rsp = '{"base":"USD","date":"2018-03-21","rates":{"RUB":57.482}}';
                break;

            case Google::class:
                $rsp = \file_get_contents(__DIR__ . '/Fixture/google.html');
                break;
        }

        return $rsp;
    }
}
