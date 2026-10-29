<?php

namespace OxCom\MagentoCurrencyServices\Test\Model\Currency\Import;

use OxCom\MagentoCurrencyServices\Model\Currency\Import\Fixer;
use OxCom\MagentoCurrencyServices\Test\Model\AbstractTestCase;
use Psr\Log\LoggerInterface;

/**
 * Class FixerTest
 *
 * @package OxCom\MagentoCurrencyServices\Test\Model\Currency\Import
 */
class FixerTest extends AbstractTestCase
{
    private const TOKEN = 'secret-fixer-token';

    /**
     * A failed request must not expose the access token in logs or admin messages.
     *
     * @throws \ReflectionException
     */
    public function testAccessTokenIsMaskedOnFailure()
    {
        $logged = [];
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->atLeastOnce())
               ->method('error')
               ->willReturnCallback(static function ($message, array $context = []) use (&$logged) {
                   $logged[] = $context;
               });

        $cArgs           = $this->om->getConstructArguments(Fixer::class, []);
        $cArgs['logger'] = $logger;

        $source = $this->getMockBuilder(Fixer::class)
                       ->setConstructorArgs($cArgs)
                       ->onlyMethods(['getAccessToken', 'getRequestOptions'])
                       ->getMock();

        $source->expects($this->any())
               ->method('getAccessToken')
               ->willReturn(static::TOKEN);

        // Port 1 on localhost refuses the connection, so the request fails without leaving the host.
        $source->expects($this->any())
               ->method('getRequestOptions')
               ->willReturnCallback(static function ($url) {
                   return [
                       CURLOPT_URL => 'http://127.0.0.1:1/?' . \parse_url($url, PHP_URL_QUERY),
                       CURLOPT_RETURNTRANSFER => 1,
                       CURLOPT_CONNECTTIMEOUT => 1,
                   ];
               });

        $method = new \ReflectionMethod($source, '_convert');
        $method->invoke($source, 'USD', 'EUR');

        $this->assertNotEmpty($logged);
        $this->assertStringNotContainsString(static::TOKEN, \var_export($logged, true));
        $this->assertStringContainsString('access_key=***', $logged[0]['url']);

        $this->assertNotEmpty($source->getMessages());
        $this->assertStringNotContainsString(static::TOKEN, \implode(PHP_EOL, $source->getMessages()));
    }
}
