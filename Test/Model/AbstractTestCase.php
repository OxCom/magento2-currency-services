<?php

namespace OxCom\MagentoCurrencyServices\Test\Model;

/**
 * Class AbstractTestCase
 *
 * @package OxCom\MagentoCurrencyServices\Test\Model
 */
abstract class AbstractTestCase extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $om;

    /**
     * @{@inheritdoc}
     */
    public function __construct($name = null, array $data = [], $dataName = '')
    {
        require_once __DIR__ . '/../Generate/function.php';
        require_once __DIR__ . '/../Generate/CurrencyFactory.php';

        parent::__construct($name, $data, $dataName);
    }

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     *odel
     */
    protected function setUp()
    {
        $this->om = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
    }

    /**
     * Tears down the fixture, for example, close a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        $this->om = null;
    }
}
