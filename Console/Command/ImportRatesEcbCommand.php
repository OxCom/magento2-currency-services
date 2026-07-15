<?php

namespace OxCom\MagentoCurrencyServices\Console\Command;

/**
 * Class ImportRatesEcbCommand
 *
 * @package OxCom\MagentoCurrencyServices\Console\Command
 */
class ImportRatesEcbCommand extends AbstractImportRatesCommand
{
    protected function configure(): void
    {
        $this->setName('oxcom:importrates:ecb')
             ->setDescription('Import currency rates from European Central Bank');

        parent::configure();
    }

    protected function getServiceCode(): string
    {
        return 'ecb';
    }
}
