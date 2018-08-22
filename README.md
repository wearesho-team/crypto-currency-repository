# CryptoCurrency repository
Package for getting actual crypto-currency information from coinmarketcap.com

List of crypto-currencies the program works with:

1. bitcoin
2. litecoin
3. tether
4. monero
5. ripple
6. kickico
7. zcash
8. waves
9. ethereum-classic
10. ethereum
11. dash
12. dogecoin
13. bitcoin-cash
        
## Usage

Create a repository

```php
<?php

$repository = new Wearesho\CryptoCurrency\Repository(
    new Cache(), // implementation of Psr\SimpleCache\CacheInterface
    new GuzzleHttp\Client()
);

```

Receive an information about actual cryptocurrencies

```php
<?php

$currencies = $repository->pullCurrency();
$globalData = $repository->pullGlobal();
$topsData = $repository->pullTops();

```

You can receive information about three categories: currencies, global and tops.
* The "currencies" section contains a list of exchage rates and other information about crypto-currencies. 
The list of available currencies is given above.

```json
[
    {
      "id": "bitcoin",
      "name": "Bitcoin",
      "symbol": "BTC",
      "available_supply": "16955212.0",
      "change_usd": 346.99392,
      "price": {
        "uah": "196860.016147",
        "usd": "7414.4",
        "btc": "1.0"
      },
      "market_cap": {
        "uah": "3337803308099",
        "usd": "125712723853",
        "btc": "16955212.0"
      },
      "volume": {
        "uah": "133792539054.6600036621",
        "usd": "5039070000.0",
        "btc": "679632.876565"
      },
      "percent_change": {
        "h1": "-0.1",
        "h24": "4.68",
        "d7": "-7.04"
      }
    },
    {
      "id": "ethereum",
      "name": "Ethereum",
      "symbol": "ETH",
      "available_supply": "98586828.0",
      "change_usd": 11.3208437,
      "price": {
        "uah": "10696.8025363",
        "usd": "402.877",
        "btc": "0.0546476"
      },
      "market_cap": {
        "uah": "1054563836134",
        "usd": "39718365667.0",
        "btc": "5391934.0"
      },
      "volume": {
        "uah": "32741412509.7",
        "usd": "1233150000.0",
        "btc": "167405.276143"
      },
      "percent_change": {
        "h1": "0.12",
        "h24": "2.81",
        "d7": "-12.77"
      }
    },
    {
      "id": "ripple",
      "name": "Ripple",
      "symbol": "XRP",
      "available_supply": "39094520623.0",
      "change_usd": 0.037950312,
      "price": {
        "uah": "14.1519687644",
        "usd": "0.53301",
        "btc": "0.00007230"
      },
      "market_cap": {
        "uah": "553264434715",
        "usd": "20837770437.0",
        "btc": "2828815.0"
      },
      "volume": {
        "uah": "8952240033.5",
        "usd": "337171000.0",
        "btc": "45772.3751062"
      },
      "percent_change": {
        "h1": "2.45",
        "h24": "7.12",
        "d7": "-8.23"
      }
    }
]
```
          
* The "global" section contains global information about the entire crypto-currency market 

```json
{
  "totalMarketCap": 276907297808,
  "totalVolume": 13180847493
}
```
        
* The "tops" section contains top 3 growing and top 3 falling currencies in the last 24 hours.
There are the tops only those currencies that are contained in the available currencies list.

```json
{
  "grow": [
      {
        "name": "Bitcoin",
        "symbol": "BTC",
        "price_usd": "7414.4",
        "percent_change": "4.68",
        "change_usd": 346.99392
      },
      {
        "name": "Dash",
        "symbol": "DASH",
        "price_usd": "333.284",
        "percent_change": "10.32",
        "change_usd": 34.3949088
      },
      {
        "name": "Bitcoin Cash",
        "symbol": "BCH",
        "price_usd": "692.299",
        "percent_change": "2.45",
        "change_usd": 16.9613255
      }
    ],
    "fall": [
      {
        "name": "Dogecoin",
        "symbol": "DOGE",
        "price_usd": "0.002896",
        "percent_change": "3.56",
        "change_usd": 0.0001030976
      },
      {
        "name": "Tether",
        "symbol": "USDT",
        "price_usd": "1.00185",
        "percent_change": "0.14",
        "change_usd": 0.00140259
      },
      {
        "name": "KickCoin",
        "symbol": "KICK",
        "price_usd": "0.0529235",
        "percent_change": "6.22",
        "change_usd": 0.0032918417
      }
    ]
}
```
       
## Contributors
[Jaroslaw Siplowski](https://github.com/siplowski)  
[Alexander Yagich](https://github.com/sashabeton)

## License
MIT