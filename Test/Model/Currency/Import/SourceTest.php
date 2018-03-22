<?php

namespace OxCom\MagentoCurrencyServices\Test\Model\Currency\Import;

use OxCom\MagentoCurrencyServices\Model\Currency\Import\Ecb;
use OxCom\MagentoCurrencyServices\Model\Currency\Import\Fixer;
use OxCom\MagentoCurrencyServices\Model\Currency\Import\Google;
use OxCom\MagentoCurrencyServices\Test\Generate\Fixtures;
use OxCom\MagentoCurrencyServices\Test\Model\AbstractTestCase;

/**
 * Class GoogleTest
 *
 * @package OxCom\MagentoCurrencyServices\Test\Model\Currency\Import
 */
class SourceTest extends AbstractTestCase
{
    /**
     * @var bool
     */
    public static $isReal;

    public static function setUpBeforeClass()
    {
        // every week switch to real or mocked response to avoid loose of source
        $date           = new \DateTime();
        $week           = $date->format("W");
        static::$isReal = $week % 2 == 0;

        if (static::$isReal) {
            fwrite(STDOUT, "!!! Test will be executed on real sources !!!" . PHP_EOL . PHP_EOL);
        }
    }

    /**
     * Perform tests on real source providers
     *
     * @dataProvider generateSource
     *
     * @param $sourceClass
     *
     * @throws \ReflectionException
     */
    public function testSources($sourceClass)
    {
        $constructArguments = $this->om->getConstructArguments($sourceClass, []);
        $source = $this->getMockBuilder($sourceClass)
            ->setConstructorArgs($constructArguments)
            ->setMethods(static::$isReal ? [] : ['request'])
            ->getMock();

        if (!static::$isReal) {
            $source->expects($this->any())->method('request')->willReturn(Fixtures::get($sourceClass));
        }

        /** @var \OxCom\MagentoCurrencyServices\Model\Currency\Import\Google $source */
        $method = new \ReflectionMethod($source, '_convert');
        $method->setAccessible(true);

        $time  = microtime(true);
        $value = $method->invoke($source, 'USD', 'RUB');
        $dx    = microtime(true) - $time;

        $this->assertTrue($dx > 1, 'Delay should be more that 1 min');
        $this->assertNotNull($value, "There is no response from source");
        $this->assertTrue($value > 0, "There should be value more than 0");
    }

    /**
     * @return array
     */
    public function generateSource()
    {
        return [
            [Google::class],
            [Fixer::class],
            [Ecb::class],
        ];
    }
}
