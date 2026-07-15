<?php

namespace OxCom\MagentoCurrencyServices\Console\Command;

/**
 * Class ImportRatesGoogleCommand
 *
 * @package OxCom\MagentoCurrencyServices\Console\Command
 */
class ImportRatesGoogleCommand extends AbstractImportRatesCommand
{
    protected function configure(): void
    {
        $this->setName('oxcom:importrates:google')
             ->setDescription('Import currency rates from Google');

        parent::configure();
    }

    protected function getServiceCode(): string
    {
        return 'google';
    }
}
