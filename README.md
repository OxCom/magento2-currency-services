# Magento2 Currency Services
This is a module that allows to update currency rates from addition external sources.

###### List of source services
1. [Finance Google](https://finance.google.com/finance/converter)
2. [Finance Yahoo](https://developer.yahoo.com/yql/console/?q=show%20tables&env=store://datatables.org/alltableswithkeys#h=select+*+from+yahoo.finance.xchange+where+pair+in+(%22USDEUR%22))
3. [European Central Bank](http://www.ecb.europa.eu/stats/policy_and_exchange_rates/euro_reference_exchange_rates/html/index.en.html)
4. [Fixer](http://fixer.io/)

## Install
N/A

## Tests
Test will be performed on real sources, so internet connection is required.

```bash
$ composer install
$ vendor/bin/phpunit -c tests/phpunit.xml
```

## Thanks
Big thanks for my wife.
