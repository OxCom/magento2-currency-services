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
     * Perform tests on real source providers
     *
     * @dataProvider generateSource
     *
     * @param string $sourceClass
     * @param string $from
     * @param string $to
     * @param double $expected
     *
     * @throws \ReflectionException
     */
    public function testSources($sourceClass, $from, $to, $expected)
    {
        $token = \getenv('FIXER_FREE_TOKEN');

        $cArgs  = $this->om->getConstructArguments($sourceClass, []);
        $source = $this->getMockBuilder($sourceClass)
                       ->setConstructorArgs($cArgs)
                       ->setMethods(static::$isReal ? ['getAccessToken'] : ['request', 'getAccessToken'])
                       ->getMock();


        if (!static::$isReal) {
            $source->expects($this->any())
                   ->method('request')
                   ->willReturn(Fixtures::get($sourceClass));
        }

        $source->expects($this->any())
               ->method('getAccessToken')
               ->willReturn($token);

        /** @var \OxCom\MagentoCurrencyServices\Model\Currency\Import\Google $source */
        $method = new \ReflectionMethod($source, '_convert');
        $method->setAccessible(true);

        $time  = \microtime(true);
        $value = $method->invoke($source, $from, $to);
        $dx    = \microtime(true) - $time;

        $this->assertTrue($dx > 1, 'Delay should be more that 1 min');
        $this->assertNotNull($value, "There is no response from source");
        $this->assertTrue($value > 0, "There should be value more than 0");

        if (!static::$isReal) {
            $this->assertEquals($expected, $value);
        }
    }

    /**
     * Perform tests on real source providers
     *
     * @dataProvider generateSource
     *
     * @param string $sourceClass
     * @param string $from
     * @param string $to
     *
     * @throws \ReflectionException
     */
    public function testSourcesWithSameCurrency($sourceClass, $from, $to)
    {
        $token = \getenv('FIXER_FREE_TOKEN');

        $cArgs  = $this->om->getConstructArguments($sourceClass, []);
        $source = $this->getMockBuilder($sourceClass)
                       ->setConstructorArgs($cArgs)
                       ->setMethods(static::$isReal ? ['getAccessToken'] : ['request', 'getAccessToken'])
                       ->getMock();

        if (!static::$isReal) {
            $source->expects($this->any())
                   ->method('request')
                   ->willReturn(Fixtures::get($sourceClass));
        }

        $source->expects($this->any())
               ->method('getAccessToken')
               ->willReturn($token);

        /** @var \OxCom\MagentoCurrencyServices\Model\Currency\Import\Google $source */
        $method = new \ReflectionMethod($source, '_convert');
        $method->setAccessible(true);

        $time  = \microtime(true);
        $value = $method->invoke($source, $from, $from);
        $dx    = \microtime(true) - $time;

        $this->assertTrue($dx > 1, 'Delay should be more that 1 min');
        $this->assertEquals(1, $value);
        $this->assertTrue($value > 0, "There should be value more than 0");
    }

    /**
     * @return array
     */
    public function generateSource()
    {
        return [
            [Google::class, 'USD', 'EUR', 0.885715],
            [Fixer::class, 'USD', 'EUR', 0.88726],
            [Ecb::class, 'USD', 'EUR', 0.892857],
        ];
    }
}
