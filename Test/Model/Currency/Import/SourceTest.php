<?php

namespace OxCom\MagentoCurrencyServices\Test\Model\Currency\Import;

use OxCom\MagentoCurrencyServices\Model\Currency\Import\BankOfCanada;
use OxCom\MagentoCurrencyServices\Model\Currency\Import\Ecb;
use OxCom\MagentoCurrencyServices\Model\Currency\Import\Fixer;
use OxCom\MagentoCurrencyServices\Model\Currency\Import\Google;
use OxCom\MagentoCurrencyServices\Test\Model\AbstractTestCase;

/**
 * Tests all configured providers against their live services.
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
     * @throws \ReflectionException
     */
    public function testSources($sourceClass, $from, $to)
    {
        $token = \getenv('FIXER_FREE_TOKEN');

        $cArgs  = $this->om->getConstructArguments($sourceClass, []);
        $source = $this->getMockBuilder($sourceClass)
                       ->setConstructorArgs($cArgs)
                       ->setMethods(['getAccessToken'])
                       ->getMock();

        $source->expects($this->any())
               ->method('getAccessToken')
               ->willReturn($token);

        /** @var \OxCom\MagentoCurrencyServices\Model\Currency\Import\Google $source */
        $method = new \ReflectionMethod($source, '_convert');
        $method->setAccessible(true);

        $time  = \microtime(true);
        $value = $method->invoke($source, $from, $to);
        $dx    = \microtime(true) - $time;

        $this->assertTrue($dx > 1, 'Delay should be more than one second');
        $this->assertNotNull($value, "There is no response from source");
        $this->assertTrue($value > 0, "There should be value more than 0");
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
                       ->setMethods(['getAccessToken'])
                       ->getMock();

        $source->expects($this->any())
               ->method('getAccessToken')
               ->willReturn($token);

        /** @var \OxCom\MagentoCurrencyServices\Model\Currency\Import\Google $source */
        $method = new \ReflectionMethod($source, '_convert');
        $method->setAccessible(true);

        $time  = \microtime(true);
        $value = $method->invoke($source, $from, $from);
        $dx    = \microtime(true) - $time;

        $this->assertTrue($dx > 1, 'Delay should be more than one second');
        $this->assertEquals(1, $value);
        $this->assertTrue($value > 0, "There should be value more than 0");
    }

    /**
     * @return array
     */
    public function generateSource()
    {
        return [
            [Google::class, 'USD', 'EUR'],
            [Fixer::class, 'USD', 'EUR'],
            [Ecb::class, 'USD', 'EUR'],
            [BankOfCanada::class, 'USD', 'EUR'],
        ];
    }
}
