<?php

namespace OxCom\MagentoCurrencyServices\Test\Console\Command;

use Magento\Directory\Model\Currency\Import\Factory as CurrencyImportFactory;
use Magento\Directory\Model\Currency\Import\ImportInterface;
use Magento\Framework\Console\Cli;
use OxCom\MagentoCurrencyServices\Console\Command\AbstractImportRatesCommand;
use OxCom\MagentoCurrencyServices\Console\Command\ImportRatesEcbCommand;
use OxCom\MagentoCurrencyServices\Console\Command\ImportRatesFixerCommand;
use OxCom\MagentoCurrencyServices\Console\Command\ImportRatesGoogleCommand;
use OxCom\MagentoCurrencyServices\Test\Model\AbstractTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Class AbstractImportRatesCommandTest
 *
 * @package OxCom\MagentoCurrencyServices\Test\Console\Command
 */
class AbstractImportRatesCommandTest extends AbstractTestCase
{
    /**
     * @param string $commandClass
     * @param string $expectedName
     * @param string $expectedServiceCode
     */
    #[DataProvider('generateCommands')]
    public function testCommandIsRegisteredWithExpectedServiceCode(
        $commandClass,
        $expectedName,
        $expectedServiceCode
    ): void {
        $importModel = $this->createMock(ImportInterface::class);
        $importModel->expects($this->once())->method('importRates');
        $importModel->expects($this->once())->method('getMessages')->willReturn([]);

        $factory = $this->createMock(CurrencyImportFactory::class);
        $factory->expects($this->once())
                ->method('create')
                ->with($expectedServiceCode)
                ->willReturn($importModel);

        /** @var AbstractImportRatesCommand $command */
        $command = new $commandClass($factory);

        $this->assertSame($expectedName, $command->getName());

        $tester   = new CommandTester($command);
        $exitCode = $tester->execute([]);

        $this->assertSame(Cli::RETURN_SUCCESS, $exitCode);
        $this->assertStringContainsString('imported successfully', $tester->getDisplay());
    }

    public function testExecuteReturnsFailureAndPrintsErrorsWhenImportReportsMessages(): void
    {
        $importModel = $this->createMock(ImportInterface::class);
        $importModel->expects($this->once())->method('importRates');
        $importModel->expects($this->once())
                    ->method('getMessages')
                    ->willReturn(["We can't retrieve a rate from source."]);

        $factory = $this->createMock(CurrencyImportFactory::class);
        $factory->expects($this->once())
                ->method('create')
                ->with('ecb')
                ->willReturn($importModel);

        $command = new ImportRatesEcbCommand($factory);
        $tester  = new CommandTester($command);

        $exitCode = $tester->execute([]);

        $this->assertSame(Cli::RETURN_FAILURE, $exitCode);
        $this->assertStringContainsString("We can't retrieve a rate", $tester->getDisplay());
    }

    /**
     * @return array
     */
    public static function generateCommands(): array
    {
        return [
            [ImportRatesEcbCommand::class, 'oxcom:importrates:ecb', 'ecb'],
            [ImportRatesFixerCommand::class, 'oxcom:importrates:fixer', 'fixer'],
            [ImportRatesGoogleCommand::class, 'oxcom:importrates:google', 'google'],
        ];
    }
}
