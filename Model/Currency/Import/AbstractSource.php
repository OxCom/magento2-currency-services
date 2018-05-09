<?php

namespace OxCom\MagentoCurrencyServices\Model\Currency\Import;

use Magento\Directory\Model\Currency\Import\AbstractImport;
use Magento\Directory\Model\CurrencyFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Class AbstractSource
 *
 * @package OxCom\MagentoCurrencyServices\Model\Currency\Import
 */
abstract class AbstractSource extends AbstractImport
{
    const SOURCE_NAME = 'abstract';
    const SOURCE_LINK = '';

    const DEFAULT_DELAY   = 1;
    const DEFAULT_TIMEOUT = 100;
    const DEFAULT_TOKEN   = '';

    /**
     * @codingStandardsIgnoreStart
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Framework\HTTP\ZendClient
     */
    protected $_httpClient;

    /**
     * @codingStandardsIgnoreStop
     *
     * @param \Magento\Directory\Model\CurrencyFactory           $currencyFactory
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(CurrencyFactory $currencyFactory, ScopeConfigInterface $scopeConfig)
    {
        parent::__construct($currencyFactory);

        $this->_scopeConfig = $scopeConfig;
        $this->_httpClient  = new \Magento\Framework\HTTP\ZendClient();
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

        $value = (int)$this->_scopeConfig->getValue(
            'currency/' . $source . '/delay',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        $value = empty($value) ? static::DEFAULT_DELAY : (int)$value;
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

        $value = (int)$this->_scopeConfig->getValue(
            'currency/' . $source . '/timeout',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
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

        $value = (int)$this->_scopeConfig->getValue(
            'currency/' . $source . '/token',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        $value = empty($value) ? static::DEFAULT_TOKEN : $value;

        return $value;
    }

    /**
     * @param string $url
     *
     * @return string
     * @throws \Zend_Http_Client_Exception
     */
    protected function request($url)
    {
        $agent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko)'
            . 'Chrome/64.0.3282.186 Safari/537.36';
        $response = $this->_httpClient
            ->setUri($url)
            ->setHeaders('User-Agent', $agent)
            ->setConfig([
                'timeout' => $this->getRequestTimeout(),
            ])->request('GET')
            ->getBody();

        return $response;
    }
}
