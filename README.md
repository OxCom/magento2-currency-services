# Magento2 Currency Services

[![Build Status](https://app.travis-ci.com/OxCom/magento2-currency-services.svg?branch=master)](https://app.travis-ci.com/OxCom/magento2-currency-services)

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

## Tests
Test will be performed on real sources, so internet connection is required.

```bash
$ composer install
$ vendor/bin/phpunit -c Test/phpunit.xml
```

Some info how to run tests in [Travis + Magento Module](https://gordonlesti.com/magento2-extension-development-with-travis-ci/)
