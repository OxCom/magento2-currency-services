<?php

namespace OxCom\MagentoCurrencyServices\Console\Command;

/**
 * Class ImportRatesFixerCommand
 *
 * @package OxCom\MagentoCurrencyServices\Console\Command
 */
class ImportRatesFixerCommand extends AbstractImportRatesCommand
{
    protected function configure(): void
    {
        $this->setName('oxcom:importrates:fixer')
             ->setDescription('Import currency rates from Fixer.io');

        parent::configure();
    }

    protected function getServiceCode(): string
    {
        return 'fixer';
    }
}
