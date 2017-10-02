<?php

namespace OxCom\CurrencyServices\Test\Model\Currency\Import;

use OxCom\CurrencyServices\Test\Model\AbstractTestCase;

/**
 * Class GoogleTest
 *
 * @package OxCom\CurrencyServices\Test\Model\Currency\Import
 */
class SourceTest extends AbstractTestCase
{
    /**
     * Perform tests on real source providers
     *
     * @dataProvider generateSource
     *
     * @param $sourceClass
     */
    public function testSources($sourceClass)
    {
        /** @var \OxCom\CurrencyServices\Model\Currency\Import\Google $source */
        $source = $this->om->getObject($sourceClass);

        $method = new \ReflectionMethod($source, '_convert');
        $method->setAccessible(true);

        $time  = microtime(true);
        $value = $method->invoke($source, 'USD', 'RUB');
        $dx    = microtime(true) - $time;

        $this->assertTrue($dx > 1);
        $this->assertNotNull($value);
        $this->assertTrue($value > 0);
    }

    /**
     * @return array
     */
    public function generateSource()
    {
        return [
            [\OxCom\CurrencyServices\Model\Currency\Import\Google::class],
            [\OxCom\CurrencyServices\Model\Currency\Import\Yahoo::class],
            [\OxCom\CurrencyServices\Model\Currency\Import\Fixer::class],
            [\OxCom\CurrencyServices\Model\Currency\Import\Ecb::class],
        ];
    }
}
