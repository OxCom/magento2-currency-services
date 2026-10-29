<?php

namespace OxCom\MagentoCurrencyServices\Model\Currency\Import;

use Magento\Directory\Model\Currency\Import\AbstractImport;
use Magento\Directory\Model\CurrencyFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Psr\Log\LoggerInterface;

/**
 * Class AbstractSource
 *
 * @package OxCom\MagentoCurrencyServices\Model\Currency\Import
 */
abstract class AbstractSource extends AbstractImport
{
    public const SCALE = 6;

    public const SOURCE_NAME = 'abstract';
    public const SOURCE_LINK = '';

    public const DEFAULT_DELAY   = 1;
    public const DEFAULT_TIMEOUT = 3;
    public const DEFAULT_TOKEN   = '';

    /**
     * @var ScopeConfigInterface
     */
    protected $config;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Why the last request() call failed, '' when it succeeded.
     *
     * @var string
     */
    private $lastRequestError = '';

    /**
     * @param CurrencyFactory $currencyFactory
     * @param ScopeConfigInterface $scopeConfig
     * @param LoggerInterface $logger
     */
    public function __construct(
        CurrencyFactory $currencyFactory,
        ScopeConfigInterface $scopeConfig,
        LoggerInterface $logger
    ) {
        parent::__construct($currencyFactory);

        $this->config = $scopeConfig;
        $this->logger = $logger;
    }

    /**
     * Implement delay between request
     */
    protected function doRequestDelay()
    {
        $source = static::SOURCE_NAME;

        if (empty($source) || $source === self::SOURCE_NAME) {
            return;
        }

        $value = (int)$this->config->getValue(
            'currency/' . $source . '/delay',
            ScopeInterface::SCOPE_STORE
        );

        $value = empty($value) ? static::DEFAULT_DELAY : (int)$value;

        $this->logger->debug('Trigger request delay', ['delay' => $value]);
        \sleep($value);
    }

    /**
     * Get request timeout
     *
     * @return int
     */
    protected function getRequestTimeout()
    {
        $source = static::SOURCE_NAME;
        if (empty($source) || $source === self::SOURCE_NAME) {
            return static::DEFAULT_TIMEOUT;
        }

        $value = (int)$this->config->getValue(
            'currency/' . $source . '/timeout',
            ScopeInterface::SCOPE_STORE
        );

        $value = empty($value) ? static::DEFAULT_TIMEOUT : $value;

        return $value;
    }

    /**
     * Get access token
     *
     * @return string
     */
    protected function getAccessToken()
    {
        $source = static::SOURCE_NAME;
        if (empty($source) || $source === self::SOURCE_NAME) {
            return static::DEFAULT_TOKEN;
        }

        $value = (string)$this->config->getValue(
            'currency/' . $source . '/token',
            ScopeInterface::SCOPE_STORE
        );

        $value = empty($value) ? static::DEFAULT_TOKEN : $value;

        return $value;
    }

    /**
     * Build the cURL option set for a GET request.
     *
     * HTTP/1.1 is forced on purpose: www.ecb.europa.eu drops the connection during the TLS/ALPN
     * handshake when a client offers HTTP/2, which surfaced as "We can't retrieve a rate from ...".
     *
     * @param string $url
     *
     * @return array
     */
    protected function getRequestOptions($url)
    {
        return [
            CURLOPT_URL => $url,
            CURLOPT_HEADER => 0,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_FOLLOWLOCATION => 1,
            CURLOPT_MAXREDIRS => 7,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_ENCODING => '',
            CURLOPT_CONNECTTIMEOUT => $this->getRequestTimeout(),
            CURLOPT_TIMEOUT => $this->getRequestTimeout() * 2,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko)'
                . 'Chrome/64.0.3282.186 Safari/537.36',
        ];
    }

    protected function request($url)
    {
        $this->lastRequestError = '';

        $options = $this->getRequestOptions($url);
        $ch      = \curl_init();

        \curl_setopt_array($ch, $options);

        $payload = \curl_exec($ch);
        $status  = (int)\curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        $errno   = \curl_errno($ch);
        $error   = $this->maskUrl(\curl_error($ch));
        $context = [
            'url' => $this->maskUrl($url),
            'status' => $status,
            'errno' => $errno,
            'error' => $error,
            'ip' => (string)\curl_getinfo($ch, CURLINFO_PRIMARY_IP),
            'time' => (float)\curl_getinfo($ch, CURLINFO_TOTAL_TIME),
            'connect_timeout' => ($options[CURLOPT_CONNECTTIMEOUT] ?? 0),
            'timeout' => ($options[CURLOPT_TIMEOUT] ?? 0),
        ];

        \curl_close($ch);

        if ($payload === false) {
            $this->lastRequestError = $this->describeTransportError($errno, $error, $options);
            $this->logger->error('Unable to request currency rates.', $context);

            return '';
        }

        if ($status >= 400) {
            $this->lastRequestError = \sprintf('HTTP %d', $status);
            $this->logger->error('Currency rate source responded with an error.', $context);

            return '';
        }

        if ($payload === '') {
            $this->lastRequestError = \sprintf('empty response, HTTP %d', $status);
            $this->logger->error('Currency rate source returned an empty response.', $context);

            return '';
        }

        $this->logger->debug('Currency rates requested.', $context + ['size' => \strlen($payload)]);

        return $payload;
    }

    /**
     * Turn a cURL failure into a short reason that can be shown to the admin user.
     *
     * @param int $errno
     * @param string $error
     * @param array $options
     *
     * @return string
     */
    protected function describeTransportError($errno, $error, array $options)
    {
        if ($errno === CURLE_OPERATION_TIMEDOUT) {
            return \sprintf(
                'request timed out (connect timeout %ds, total timeout %ds): %s',
                ($options[CURLOPT_CONNECTTIMEOUT] ?? 0),
                ($options[CURLOPT_TIMEOUT] ?? 0),
                $error
            );
        }

        return \sprintf('cURL error %d: %s', $errno, $error);
    }

    /**
     * Why the last request() call failed, '' when it succeeded.
     *
     * @return string
     */
    protected function getLastRequestError()
    {
        return $this->lastRequestError;
    }

    /**
     * Collect the admin message for a failed source, with the reason when one is known.
     * The reason is masked too: cURL error text may quote the request URL.
     *
     * @param string $url
     * @param string $reason
     */
    protected function addRetrieveError($url, $reason = '')
    {
        $url    = $this->maskUrl($url);
        $reason = $this->maskUrl($reason);

        $this->_messages[] = $reason === ''
            ? \__("We can't retrieve a rate from %1.", $url)
            : \__("We can't retrieve a rate from %1: %2", $url, $reason);
    }

    /**
     * Hide the access token so a URL can be written to logs and admin messages.
     *
     * @param string $url
     *
     * @return string
     */
    protected function maskUrl($url)
    {
        return \preg_replace('/(access_key=)[^&]*/', '$1***', (string)$url);
    }

    /**
     * @return LoggerInterface
     */
    protected function getLogger()
    {
        return $this->logger;
    }

    protected function processPayload(string $content, $default = null)
    {
        try {
            $flags = \defined('JSON_THROW_ON_ERROR') ? JSON_THROW_ON_ERROR : 0;
            $payload = \json_decode($content, true, 512, $flags);
        } catch (\Throwable $e) {
            $this->logger->debug(__METHOD__ . ': Unable to decode content.', [
                'content' => $content,
                'exception' => $e,
            ]);

            $payload = $default;
        }

        return $payload;
    }
}
