<?php

namespace OxCom\MagentoCurrencyServices\Test\Model\Currency\Import;

use OxCom\MagentoCurrencyServices\Test\Model\AbstractTestCase;

/**
 * Class GoogleTest
 *
 * @package OxCom\MagentoCurrencyServices\Test\Model\Currency\Import
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
        /** @var \OxCom\MagentoCurrencyServices\Model\Currency\Import\Google $source */
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
            [\OxCom\MagentoCurrencyServices\Model\Currency\Import\Google::class],
            [\OxCom\MagentoCurrencyServices\Model\Currency\Import\Yahoo::class],
            [\OxCom\MagentoCurrencyServices\Model\Currency\Import\Fixer::class],
            [\OxCom\MagentoCurrencyServices\Model\Currency\Import\Ecb::class],
        ];
    }
}
