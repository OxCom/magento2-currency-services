<?php

namespace OxCom\MagentoCurrencyServices\Console\Command;

use Magento\Directory\Model\Currency\Import\Factory as CurrencyImportFactory;
use Magento\Framework\Console\Cli;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class AbstractImportRatesCommand
 *
 * @package OxCom\MagentoCurrencyServices\Console\Command
 */
abstract class AbstractImportRatesCommand extends Command
{
    /**
     * @var CurrencyImportFactory
     */
    private $currencyImportFactory;

    /**
     * @param CurrencyImportFactory $currencyImportFactory
     * @param string|null $name
     */
    public function __construct(CurrencyImportFactory $currencyImportFactory, $name = null)
    {
        parent::__construct($name);

        $this->currencyImportFactory = $currencyImportFactory;
    }

    /**
     * Service code matching the `servicesConfig` key registered in etc/di.xml
     *
     * @return string
     */
    abstract protected function getServiceCode(): string;

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $importModel = $this->currencyImportFactory->create($this->getServiceCode());
        $importModel->importRates();

        $errors = $importModel->getMessages();

        foreach ($errors as $error) {
            $output->writeln('<error>' . (string)$error . '</error>');
        }

        if (!empty($errors)) {
            return Cli::RETURN_FAILURE;
        }

        $output->writeln('<info>Currency rates imported successfully.</info>');

        return Cli::RETURN_SUCCESS;
    }
}
