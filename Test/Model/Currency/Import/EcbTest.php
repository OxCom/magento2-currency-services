<?php

namespace OxCom\MagentoCurrencyServices\Test\Model\Currency\Import;

use OxCom\MagentoCurrencyServices\Model\Currency\Import\Ecb;
use OxCom\MagentoCurrencyServices\Test\Generate\Fixtures;
use OxCom\MagentoCurrencyServices\Test\Model\AbstractTestCase;

/**
 * Class EcbTest
 *
 * Covers the ECB source: the daily XML feed, the SDMX CSV fallback and the failure paths.
 *
 * @package OxCom\MagentoCurrencyServices\Test\Model\Currency\Import
 */
class EcbTest extends AbstractTestCase
{
    /**
     * Payload returned by www.ecb.europa.eu when the feed is not served (redirect/login page).
     */
    private const BROKEN_PAYLOAD = '<!DOCTYPE html><html lang="en"><head><base href="/login/"></head></html>';

    /**
     * The daily XML feed is the primary source and no fallback request is made.
     *
     * @throws \ReflectionException
     */
    public function testDailyXmlIsUsedAsPrimarySource()
    {
        $source = $this->createEcbMock([
            Ecb::SOURCE_LINK => Fixtures::get(Ecb::class),
            Ecb::SOURCE_LINK_FALLBACK => '',
        ]);

        $this->assertEquals(0.892857, $this->convert($source, 'USD', 'EUR'));
        $this->assertEmpty($source->getMessages());
    }

    /**
     * When the XML feed answers with something that is not the feed, the SDMX CSV is used.
     *
     * @throws \ReflectionException
     */
    public function testFallbackIsUsedWhenDailyXmlIsUnusable()
    {
        $source = $this->createEcbMock([
            Ecb::SOURCE_LINK => static::BROKEN_PAYLOAD,
            Ecb::SOURCE_LINK_FALLBACK => $this->buildCsv([
                ['USD', \date('Y-m-d'), '1.1200'],
                ['GBP', \date('Y-m-d'), '0.90430'],
            ]),
        ]);

        $this->assertEquals(0.892857, $this->convert($source, 'USD', 'EUR'));
        $this->assertEmpty($source->getMessages());
    }

    /**
     * Cross rates are triangulated from the fallback payload as well.
     *
     * @throws \ReflectionException
     */
    public function testFallbackSupportsCrossRates()
    {
        $source = $this->createEcbMock([
            Ecb::SOURCE_LINK => '',
            Ecb::SOURCE_LINK_FALLBACK => $this->buildCsv([
                ['USD', \date('Y-m-d'), '1.1200'],
                ['GBP', \date('Y-m-d'), '0.90430'],
            ]),
        ]);

        // 1 USD = 0.90430 / 1.1200 GBP, rounded to AbstractSource::SCALE
        $this->assertEquals(0.80741, $this->convert($source, 'USD', 'GBP'));
        $this->assertEmpty($source->getMessages());
    }

    /**
     * Discontinued series are published with old observation dates and must never be imported.
     *
     * @throws \ReflectionException
     */
    public function testStaleFallbackObservationsAreIgnored()
    {
        $stale = \date('Y-m-d', \strtotime('-' . (Ecb::MAX_RATE_AGE_DAYS + 1) . ' days'));

        $source = $this->createEcbMock([
            Ecb::SOURCE_LINK => static::BROKEN_PAYLOAD,
            Ecb::SOURCE_LINK_FALLBACK => $this->buildCsv([['USD', $stale, '1.1200']]),
        ]);

        $this->assertEquals(0.0, $this->convert($source, 'USD', 'EUR'));
        $this->assertNotEmpty($source->getMessages());
    }

    /**
     * Non EUR denominated observations are not rates against the feed base currency.
     *
     * @throws \ReflectionException
     */
    public function testFallbackIgnoresForeignDenominatedObservations()
    {
        $source = $this->createEcbMock([
            Ecb::SOURCE_LINK => static::BROKEN_PAYLOAD,
            Ecb::SOURCE_LINK_FALLBACK => $this->buildCsv([['USD', \date('Y-m-d'), '1.1200', 'CHF']]),
        ]);

        $this->assertEquals(0.0, $this->convert($source, 'USD', 'EUR'));
        $this->assertNotEmpty($source->getMessages());
    }

    /**
     * Both sources down: an error message is collected instead of an exception.
     *
     * @throws \ReflectionException
     */
    public function testMessageIsCollectedWhenBothSourcesFail()
    {
        $source = $this->createEcbMock([
            Ecb::SOURCE_LINK => '',
            Ecb::SOURCE_LINK_FALLBACK => '',
        ]);

        $this->assertEquals(0.0, $this->convert($source, 'USD', 'EUR'));

        $messages = $source->getMessages();
        $this->assertCount(2, $messages);
        $this->assertStringContainsString(Ecb::SOURCE_LINK, (string)$messages[0]);
        $this->assertStringContainsString(Ecb::SOURCE_LINK_FALLBACK, (string)$messages[1]);
    }

    /**
     * Every failed feed is reported together with the reason, not only the last tried address.
     *
     * @throws \ReflectionException
     */
    public function testMessagesExplainWhyEachSourceFailed()
    {
        $source = $this->createEcbMock([
            Ecb::SOURCE_LINK => static::BROKEN_PAYLOAD,
            Ecb::SOURCE_LINK_FALLBACK => '',
        ]);

        $this->convert($source, 'USD', 'EUR');

        $messages = $source->getMessages();
        $this->assertCount(2, $messages);
        $this->assertStringContainsString('unexpected response format', (string)$messages[0]);
        $this->assertStringContainsString('empty response', (string)$messages[1]);
    }

    /**
     * A transport failure keeps the cURL reason so it can reach the admin message.
     *
     * @throws \ReflectionException
     */
    public function testRequestKeepsTransportError()
    {
        $source  = $this->createEcbMock(null);
        $request = new \ReflectionMethod($source, 'request');
        $error   = new \ReflectionMethod($source, 'getLastRequestError');

        // nothing listens on port 1, the connection is refused right away
        $this->assertSame('', $request->invoke($source, 'http://127.0.0.1:1/'));
        $this->assertStringStartsWith('cURL error ' . CURLE_COULDNT_CONNECT, $error->invoke($source));
    }

    /**
     * Timeouts name the configured limits, so the admin knows which setting to raise.
     *
     * @throws \ReflectionException
     */
    public function testTimeoutReasonNamesConfiguredLimits()
    {
        $source   = $this->createEcbMock([]);
        $describe = new \ReflectionMethod($source, 'describeTransportError');
        $options  = [CURLOPT_CONNECTTIMEOUT => 3, CURLOPT_TIMEOUT => 6];

        $reason = $describe->invoke($source, CURLE_OPERATION_TIMEDOUT, 'Connection timed out', $options);

        $this->assertStringContainsString('timed out (connect timeout 3s, total timeout 6s)', $reason);
    }

    /**
     * The same currency never triggers a request.
     *
     * @throws \ReflectionException
     */
    public function testSameCurrencyDoesNotRequestAnything()
    {
        $source = $this->createEcbMock([]);

        $this->assertEquals(1, $this->convert($source, 'USD', 'USD'));
        $this->assertEmpty($source->getMessages());
    }

    /**
     * Feed addresses must be HTTPS: the plain HTTP address only answers with a redirect.
     */
    public function testFeedAddressesUseHttps()
    {
        $this->assertStringStartsWith('https://', Ecb::SOURCE_LINK);
        $this->assertStringStartsWith('https://', Ecb::SOURCE_LINK_FALLBACK);
    }

    /**
     * HTTP/1.1 must stay forced: the ECB host aborts the connection on HTTP/2.
     *
     * @throws \ReflectionException
     */
    public function testRequestForcesHttpOneDotOne()
    {
        $source = $this->createEcbMock([]);
        $method = new \ReflectionMethod($source, 'getRequestOptions');

        $options = $method->invoke($source, Ecb::SOURCE_LINK);

        $this->assertSame(CURL_HTTP_VERSION_1_1, $options[CURLOPT_HTTP_VERSION]);
        $this->assertSame(Ecb::SOURCE_LINK, $options[CURLOPT_URL]);
    }

    /**
     * Real mode only: verify the live feed still parses.
     *
     * @throws \ReflectionException
     */
    public function testLiveDailyFeedIsParsable()
    {
        if (!static::$isReal) {
            $this->markTestSkipped('Live source checks run in real mode only.');
        }

        $source = $this->createEcbMock(null);
        $value  = $this->convert($source, 'USD', 'EUR');

        $this->assertEmpty($source->getMessages());
        $this->assertTrue($value > 0, 'There should be value more than 0');
    }

    /**
     * Invoke the protected _convert().
     *
     * @param \PHPUnit\Framework\MockObject\MockObject $source
     * @param string $from
     * @param string $to
     *
     * @return float
     *
     * @throws \ReflectionException
     */
    private function convert($source, $from, $to)
    {
        $method = new \ReflectionMethod($source, '_convert');

        return $method->invoke($source, $from, $to);
    }

    /**
     * Build an SDMX CSV payload in the shape returned by the ECB Data Portal.
     *
     * @param array $rows list of [currency, period, rate, denom?]
     *
     * @return string
     */
    private function buildCsv(array $rows)
    {
        $lines = ['KEY,FREQ,CURRENCY,CURRENCY_DENOM,EXR_TYPE,EXR_SUFFIX,TIME_PERIOD,OBS_VALUE'];

        foreach ($rows as $row) {
            [$currency, $period, $rate] = $row;
            $denom = $row[3] ?? 'EUR';

            $lines[] = \sprintf(
                'EXR.D.%1$s.%2$s.SP00.A,D,%1$s,%2$s,SP00,A,%3$s,%4$s',
                $currency,
                $denom,
                $period,
                $rate
            );
        }

        return \implode(PHP_EOL, $lines) . PHP_EOL;
    }

    /**
     * Build an Ecb mock. Pass null to let it perform real requests.
     *
     * @param array|null $payloads url => payload
     *
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    private function createEcbMock($payloads)
    {
        $methods = $payloads === null ? ['getAccessToken'] : ['request', 'getAccessToken'];
        $cArgs   = $this->om->getConstructArguments(Ecb::class, []);

        $source = $this->getMockBuilder(Ecb::class)
                       ->setConstructorArgs($cArgs)
                       ->onlyMethods($methods)
                       ->getMock();

        if ($payloads !== null) {
            $source->expects($this->any())
                   ->method('request')
                   ->willReturnCallback(static function ($url) use ($payloads) {
                       return $payloads[$url] ?? '';
                   });
        }

        $source->expects($this->any())
               ->method('getAccessToken')
               ->willReturn('');

        return $source;
    }
}
