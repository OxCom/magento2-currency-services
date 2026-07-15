<?php

namespace OxCom\MagentoCurrencyServices\Test\Model\Currency\Import;

use OxCom\MagentoCurrencyServices\Model\Currency\Import\Ecb;
use OxCom\MagentoCurrencyServices\Model\Currency\Import\Fixer;
use OxCom\MagentoCurrencyServices\Model\Currency\Import\Google;
use OxCom\MagentoCurrencyServices\Test\Generate\Fixtures;
use OxCom\MagentoCurrencyServices\Test\Model\AbstractTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Class SourceTest
 *
 * @package OxCom\MagentoCurrencyServices\Test\Model\Currency\Import
 */
class SourceTest extends AbstractTestCase
{
    /**
     * Perform tests on source providers
     *
     * @param string $sourceClass
     * @param string $from
     * @param string $to
     * @param double $expected
     *
     * @throws \ReflectionException
     */
    #[DataProvider('generateSource')]
    public function testSources($sourceClass, $from, $to, $expected)
    {
        $source = $this->createSourceMock($sourceClass);

        /** @var \OxCom\MagentoCurrencyServices\Model\Currency\Import\AbstractSource $source */
        $method = new \ReflectionMethod($source, '_convert');

        $time  = \microtime(true);
        $value = $method->invoke($source, $from, $to);
        $dx    = \microtime(true) - $time;

        $this->assertTrue($dx > 1, 'Delay should be more that 1 sec');
        $this->assertNotNull($value, "There is no response from source");
        $this->assertTrue($value > 0, "There should be value more than 0");

        if (!static::$isReal) {
            $this->assertEquals($expected, $value);
        }
    }

    /**
     * Perform tests on source providers with the same source and target currency
     *
     * @param string $sourceClass
     * @param string $from
     *
     * @throws \ReflectionException
     */
    #[DataProvider('generateSource')]
    public function testSourcesWithSameCurrency($sourceClass, $from)
    {
        $source = $this->createSourceMock($sourceClass);

        /** @var \OxCom\MagentoCurrencyServices\Model\Currency\Import\AbstractSource $source */
        $method = new \ReflectionMethod($source, '_convert');

        $time  = \microtime(true);
        $value = $method->invoke($source, $from, $from);
        $dx    = \microtime(true) - $time;

        $this->assertTrue($dx > 1, 'Delay should be more that 1 sec');
        $this->assertEquals(1, $value);
        $this->assertTrue($value > 0, "There should be value more than 0");
    }

    /**
     * @return array
     */
    public static function generateSource(): array
    {
        return [
            [Google::class, 'USD', 'EUR', 0.885715],
            [Fixer::class, 'USD', 'EUR', 0.88726],
            [Ecb::class, 'USD', 'EUR', 0.892857],
        ];
    }

    /**
     * Build a source mock: fixture-backed unless real mode is enabled.
     *
     * @param string $sourceClass
     *
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    protected function createSourceMock($sourceClass)
    {
        $token   = \getenv('FIXER_FREE_TOKEN');
        $methods = static::$isReal ? ['getAccessToken'] : ['request', 'getAccessToken'];

        $cArgs  = $this->om->getConstructArguments($sourceClass, []);
        $source = $this->getMockBuilder($sourceClass)
                       ->setConstructorArgs($cArgs)
                       ->onlyMethods($methods)
                       ->getMock();

        if (!static::$isReal) {
            $source->expects($this->any())
                   ->method('request')
                   ->willReturn(Fixtures::get($sourceClass));
        }

        $source->expects($this->any())
               ->method('getAccessToken')
               ->willReturn($token);

        return $source;
    }
}
