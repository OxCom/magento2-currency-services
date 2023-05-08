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
    const SCALE = 6;

    const SOURCE_NAME = 'abstract';
    const SOURCE_LINK = '';

    const DEFAULT_DELAY   = 1;
    const DEFAULT_TIMEOUT = 3;
    const DEFAULT_TOKEN   = '';

    /**
     * @var ScopeConfigInterface
     */
    protected $config;

    /**
     * @param CurrencyFactory $currencyFactory
     * @param ScopeConfigInterface $scopeConfig
     * @param LoggerInterface $logger
     */
    public function __construct(CurrencyFactory $currencyFactory, ScopeConfigInterface $scopeConfig, LoggerInterface $logger)
    {
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
        sleep($value);
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
     * @return int|string
     */
    protected function getAccessToken()
    {
        $source = static::SOURCE_NAME;
        if (empty($source) || $source === self::SOURCE_NAME) {
            return static::DEFAULT_TOKEN;
        }

        $value = (int)$this->config->getValue(
            'currency/' . $source . '/token',
            ScopeInterface::SCOPE_STORE
        );

        $value = empty($value) ? static::DEFAULT_TOKEN : $value;

        return $value;
    }

    /**
     * @param string $url
     *
     * @return string
     */
    protected function request($url)
    {
        $ch = \curl_init();

        \curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_HEADER => 0,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_FOLLOWLOCATION => 1,
            CURLOPT_MAXREDIRS => 7,
            CURLOPT_CONNECTTIMEOUT => $this->getRequestTimeout(),
            CURLOPT_TIMEOUT => $this->getRequestTimeout() * 2,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko)'
                . 'Chrome/64.0.3282.186 Safari/537.36'
        ]);

        $payload = \curl_exec($ch);

        if ($payload === false) {
            $this->logger->error('Unable to request currency rates.', [
                'url' => $url,
                'error' => \curl_error($ch),
            ]);

            return '';
        }

        \curl_close($ch);

        return $payload;
    }

    protected function processPayload(string $content, mixed $default = null): mixed
    {
        try {
            $flags = defined('JSON_THROW_ON_ERROR') ? JSON_THROW_ON_ERROR : 0;
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
