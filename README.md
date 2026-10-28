# Magento2 Currency Services

[![Build Status](https://app.travis-ci.com/OxCom/magento2-currency-services.svg?branch=master)](https://app.travis-ci.com/OxCom/magento2-currency-services)

This is a module that allows to update currency rates from addition external sources.

###### List of source services
1. [Google Finance](https://www.google.com/finance/quote/USD-EUR)
2. [European Central Bank](https://www.ecb.europa.eu/stats/policy_and_exchange_rates/euro_reference_exchange_rates/html/index.en.html)
3. [Fixer](https://fixer.io/)
4. [Bank of Canada](https://www.bankofcanada.ca/valet/docs)

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

2. **Google Finance**

    Google does not provide a public currency-rates API. This integration parses
    the requested currency pair from the Google Finance quote page.
    
    Be aware that Google may change its HTML structure at any time.

3. **European Central Bank**

    ```
    The reference rates are usually updated around 16:00 CET on every working 
    day, except on TARGET closing days. They are based on a regular daily 
    concertation procedure between central banks across Europe, which normally 
    takes place at 14:15 CET.
    ```
    
    Be aware about low refresh rate

4. **Bank of Canada**

    The keyless Valet API publishes indicative business-daily rates for selected
    currencies. Rates are based on Canadian dollars and are normally published
    by 16:30 ET.

## Install
```bash
$ composer require oxcom/magento2-currency-services
$ bin/magento module:enable OxCom_MagentoCurrencyServices
$ bin/magento setup:upgrade
$ bin/magento setup:di:compile
```

## Tests
Tests always use the live provider services, so an internet connection and a
`FIXER_FREE_TOKEN` environment variable are required.

```bash
$ composer install
$ FIXER_FREE_TOKEN=your-token vendor/bin/phpunit -c Test/phpunit.xml
```

GitHub Actions runs the live provider suite against `master` every Monday and
Friday at 06:00 UTC.

