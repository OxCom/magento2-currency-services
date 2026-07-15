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

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        require_once __DIR__ . '/../Generate/function.php';
        require_once __DIR__ . '/../Generate/CurrencyFactory.php';

        static::$isReal = static::detectRealMode();

        if (static::$isReal) {
            \fwrite(STDOUT, "!!! Test will be executed on real sources !!!" . PHP_EOL . PHP_EOL);
        }
    }

    /**
     * Decide if tests should hit real endpoints or use fixtures.
     *
     * CI always runs against fixtures. Locally, every second week the tests
     * run against real sources to detect provider changes early.
     */
    protected static function detectRealMode(): bool
    {
        if (\getenv('CI')) {
            return false;
        }

        $date = new \DateTime();
        $week = (int)$date->format("W");

        return $week % 2 === 0;
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
