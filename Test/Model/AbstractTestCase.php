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
     * Tests use fixtures unless live provider checks are explicitly enabled.
     */
    protected static function detectRealMode(): bool
    {
        return \getenv('CURRENCY_SERVICES_LIVE_TESTS') === '1';
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
