# Magento2 Currency Services

[![CI](https://github.com/OxCom/magento2-currency-services/actions/workflows/ci.yml/badge.svg)](https://github.com/OxCom/magento2-currency-services/actions/workflows/ci.yml)

This is a module that allows to update currency rates from addition external sources.

###### List of source services
1. [Finance Google](https://finance.google.com/finance/converter)
2. [European Central Bank](http://www.ecb.europa.eu/stats/policy_and_exchange_rates/euro_reference_exchange_rates/html/index.en.html)
3. [Fixer](http://fixer.io/)

###### Notes
1. **Fixer**
    ```
    [The old, deprecated Fixer API will be discontinued on June 1st, 2018]
    We are happy to announce the complete relaunch of fixer.io into a more 
    stable, more secure, and much more advanced currency & exchange rate 
    conversion API platform. While the core structure of our API remains 
    unchanged, all users of the legacy Fixer API will be required to sign 
    up for a free API access key and perform a few simple changes to their 
    integration. To learn more about the changes that are required.
    ```
    
    Be aware that free access to Fixer API has limited functionality.

    You can setup Access Token for Fixer API in your administration panel 
    for Magento.

2. **Finance Google**

    New changes were introduces by Google and now it's possible only parse 
    HTML to get required information.
    
    Be aware about that Google may change it HTML structure at any time

3. **European Central Bank**

    ```
    The reference rates are usually updated around 16:00 CET on every working 
    day, except on TARGET closing days. They are based on a regular daily 
    concertation procedure between central banks across Europe, which normally 
    takes place at 14:15 CET.
    ```
    
    Be aware about low refresh rate

## Install
```bash
$ composer require oxcom/magento2-currency-services
$ bin/magento module:enable OxCom_MagentoCurrencyServices
$ bin/magento setup:upgrade
$ bin/magento setup:di:compile
```

## CLI commands
Rates can be imported manually per source, without waiting for cron or using
the admin panel:

```bash
$ bin/magento oxcom:importrates:ecb
$ bin/magento oxcom:importrates:fixer
$ bin/magento oxcom:importrates:google
```

Each command fetches rates from the given source and saves them, same as
admin System > Currency Rates > Import Now. Non-zero exit code + error
output if a rate can't be retrieved.

Commands are registered via DI, so after install run `setup:upgrade` (and
`setup:di:compile` in production mode) / flush cache before they show up in
`bin/magento list`.

## Tests
By default, local test runs alternate weekly between real sources (internet
connection required) and local fixtures. Setting the `CI` environment variable
forces fixture (mocked) mode — no network access needed:

```bash
$ composer install
$ CI=1 vendor/bin/phpunit -c Test/phpunit.xml --no-coverage
```

Composer scripts are available for all checks:

```bash
$ composer cs    # phpcs (PSR-12 + Slevomat)
$ composer stan  # phpstan analyse
$ composer test  # phpunit (no coverage)
```
