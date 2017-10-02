# Magento2 Currency Services
[![Build Status](https://travis-ci.org/OxCom/magento-currency-services.svg?branch=master)](https://travis-ci.org/OxCom/magento-currency-services)

This is a module that allows to update currency rates from addition external sources.

###### List of source services
1. [Finance Google](https://finance.google.com/finance/converter)
2. [Finance Yahoo](https://developer.yahoo.com/yql/console/?q=show%20tables&env=store://datatables.org/alltableswithkeys#h=select+*+from+yahoo.finance.xchange+where+pair+in+(%22USDEUR%22))
3. [European Central Bank](http://www.ecb.europa.eu/stats/policy_and_exchange_rates/euro_reference_exchange_rates/html/index.en.html)
4. [Fixer](http://fixer.io/)

## Install
```bash
$ composer require oxcom/magento-currency-services
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

## Thanks
Big thanks for my wife.
