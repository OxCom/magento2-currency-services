<?php

namespace OxCom\MagentoCurrencyServices\Test\Model;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * Class AbstractTestCase
 *
 * @package OxCom\MagentoCurrencyServices\Test\Model
 */
abstract class AbstractTestCase extends TestCase
{
    /**
     * @var bool
     */
    public static $isReal;

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

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        // every week switch to real or mocked response to avoid loose of source
        $date           = new \DateTime();
        $week           = $date->format("W");
        static::$isReal = $week % 2 === 0;

        if (static::$isReal) {
            \fwrite(STDOUT, "!!! Test will be executed on real sources !!!" . PHP_EOL . PHP_EOL);
        }
    }

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->om = new ObjectManager($this);
    }

    /**
     * Tears down the fixture, for example, close a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown(): void
    {
        $this->om = null;
    }
}
