<?php

namespace OxCom\CurrencyServices\Model\Currency\Import;

/**
 * Class AbstractSource
 *
 * @package OxCom\CurrencyServices\Model\Currency\Import
 */
abstract class AbstractSource extends \Magento\Directory\Model\Currency\Import\AbstractImport
{
    const SOURCE_NAME = 'abstract';
    const SOURCE_LINK = '';

    const DEFAULT_DELAY   = 1;
    const DEFAULT_TIMEOUT = 100;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Framework\HTTP\ZendClient
     */
    protected $_httpClient;

    /**
     * @param \Magento\Directory\Model\CurrencyFactory           $currencyFactory
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
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
            'currency/google/timeout',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        $value = empty($value) ? static::DEFAULT_TIMEOUT : $value;

        return $value;
    }

    /**
     * @param string $url
     *
     * @return string
     */
    protected function request($url)
    {
        $response = $this->_httpClient
                        ->setUri($url)
                        ->setConfig([
                            'timeout' => $this->getRequestTimeout(),
                        ])->request('GET')
                        ->getBody();

        return $response;
    }
}
