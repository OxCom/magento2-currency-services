<?php

namespace OxCom\MagentoCurrencyServices\Test\Model\Currency\Import;

use Magento\Framework\App\Config\ScopeConfigInterface;
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
        $record = static function ($message, array $context = []) use (&$logged) {
            $logged[] = $context;
        };

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->atLeastOnce())
               ->method('error')
               ->willReturnCallback($record);

        // any level may end up in a shared log file, so all of them are checked
        foreach (['debug', 'info', 'notice', 'warning'] as $level) {
            $logger->expects($this->any())
                   ->method($level)
                   ->willReturnCallback($record);
        }

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
        $this->assertStringContainsString('access_key=***', \array_column($logged, 'url')[0]);

        $this->assertNotEmpty($source->getMessages());
        $this->assertStringNotContainsString(static::TOKEN, \implode(PHP_EOL, $source->getMessages()));
    }

    /**
     * A failure reason that quotes the request URL must not leak the token into admin messages.
     *
     * @throws \ReflectionException
     */
    public function testAccessTokenIsMaskedInFailureReason()
    {
        $cArgs  = $this->om->getConstructArguments(Fixer::class, []);
        $source = $this->getMockBuilder(Fixer::class)
                       ->setConstructorArgs($cArgs)
                       ->onlyMethods(['getAccessToken'])
                       ->getMock();

        $url    = 'https://data.fixer.io/api/latest?access_key=' . static::TOKEN;
        $method = new \ReflectionMethod($source, 'addRetrieveError');
        $method->invoke($source, $url, 'cURL error 3: bad URL ' . $url);

        $messages = \implode(PHP_EOL, $source->getMessages());

        $this->assertStringNotContainsString(static::TOKEN, $messages);
        $this->assertStringContainsString('access_key=***', $messages);
    }

    /**
     * Fixer keys are alphanumeric, so the configured token must be passed on unchanged.
     *
     * @throws \ReflectionException
     */
    public function testAccessTokenIsReadAsString()
    {
        $config = $this->createMock(ScopeConfigInterface::class);
        $config->expects($this->any())
               ->method('getValue')
               ->willReturnCallback(static function ($path) {
                   return $path === 'currency/fixer/token' ? 'a1b2c3d4e5f6' : null;
               });

        $cArgs                = $this->om->getConstructArguments(Fixer::class, []);
        $cArgs['scopeConfig'] = $config;

        $source = new Fixer(...\array_values($cArgs));
        $method = new \ReflectionMethod($source, 'getAccessToken');

        $this->assertSame('a1b2c3d4e5f6', $method->invoke($source));
    }
}
