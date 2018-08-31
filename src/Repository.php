<?php

namespace Wearesho\CryptoCurrency;

use GuzzleHttp\ClientInterface;
use Psr\SimpleCache\CacheInterface;
use yii\base;

/**
 * Class Repository
 * @package Wearesho\CryptoCurrency
 */
class Repository extends base\BaseObject
{
    protected const BASE_URI = 'https://api.coinmarketcap.com/v1/';
    protected const CACHE_TIME = 60 * 60; // 60 minutes

    /** @var ClientInterface */
    protected $client;

    /** @var CacheInterface */
    protected $cache;

    /** @var string[] */
    public $allowedCurrencies = [
        Currency::bitcoin,
        Currency::litecoin,
        Currency::tether,
        Currency::monero,
        Currency::ripple,
        Currency::kickico,
        Currency::zcash,
        Currency::waves,
        Currency::ethereumClassic,
        Currency::ethereum,
        Currency::dash,
        Currency::dogecoin,
        Currency::bitcoinCash,
    ];

    public function __construct(CacheInterface $cache, ClientInterface $client, array $config = [])
    {
        parent::__construct($config);
        $this->client = $client;
        $this->cache = $cache;
    }

    public function pullCurrency(): array
    {
        $cacheKey = $this->buildCacheKey(CacheKey::CURRENCY);

        $cachedValue = $this->cache->get($cacheKey);
        if (is_array($cachedValue)) {
            return $cachedValue;
        }

        $resultUah = json_decode(
            $this->client->request('GET', self::BASE_URI . 'ticker/?convert=uah&limit=0')
                ->getBody()
                ->getContents(),
            true
        );

        $resultBtc = json_decode(
            $this->client->request('GET', self::BASE_URI . 'ticker/?convert=btc&limit=0')
                ->getBody()
                ->getContents(),
            true
        );

        $resultFilteredUah = array_filter($resultUah, function (array $item): bool {
            return in_array($item['id'], $this->allowedCurrencies);
        });

        $resultFilteredBtc = array_filter($resultBtc, function (array $item): bool {
            return in_array($item['id'], $this->allowedCurrencies);
        });

        $result = array_map(function (array $item_uah, array $item_btc): Entities\Currency {
            return new Entities\Currency([
                'id' => $item_uah['id'],
                'name' => $item_uah['name'],
                'symbol' => $item_uah['symbol'],
                'price' => new Containers\PriceContainer([
                    'uah' => $item_uah['price_uah'],
                    'btc' => $item_uah['price_btc'],
                    'usd' => $item_uah['price_usd']
                ]),
                'available_supply' => $item_uah['available_supply'],
                'market_cap' => new Containers\PriceContainer([
                    'uah' => $item_uah['market_cap_uah'],
                    'btc' => $item_btc['market_cap_btc'],
                    'usd' => $item_uah['market_cap_usd']
                ]),
                'percent_change' => new Containers\ChangeContainer([
                    'h1' => $item_uah['percent_change_1h'],
                    'h24' => $item_uah['percent_change_24h'],
                    'd7' => $item_uah['percent_change_7d']
                ]),
                'volume' => new Containers\PriceContainer([
                    'uah' => $item_uah['24h_volume_uah'],
                    'btc' => $item_btc['24h_volume_btc'],
                    'usd' => $item_uah['24h_volume_usd']
                ]),
                "change_usd" => ($item_uah['percent_change_24h'] * $item_uah['price_usd']) / 100
            ]);
        }, $resultFilteredUah, $resultFilteredBtc);

        $this->cache->set($cacheKey, $result, static::CACHE_TIME);
        $this->cache->set(
            $this->buildCacheKey(CacheKey::UPDATE_TIME),
            (new \DateTime())->format('Y-m-d H:i:s')
        );

        return $result;
    }

    public function pullGlobal(): Entities\GlobalData
    {
        $cacheKey = $this->buildCacheKey(CacheKey::GLOBAL_DATA);

        $cachedValue = $this->cache->get($cacheKey);
        if ($cachedValue) {
            return $cachedValue;
        }

        $global = json_decode(
            $this->client->request('GET', self::BASE_URI . 'global/')
                ->getBody()
                ->getContents(),
            true
        );

        $result = new Entities\GlobalData([
            'totalMarketCap' => $global['total_market_cap_usd'],
            'totalVolume' => $global['total_24h_volume_usd']
        ]);

        $this->cache->set($cacheKey, $result, static::CACHE_TIME);
        $this->cache->set(
            $this->buildCacheKey(CacheKey::UPDATE_TIME),
            (new \DateTime())->format('Y-m-d H:i:s')
        );

        return $result;
    }

    public function generateTops(array $currenciesList): array
    {
        $sortedChanges = $this->getChangesSorted($currenciesList);

        $result = [
            'grow' => array_map(function (Entities\Currency $item): Entities\TopData {
                return new Entities\TopData([
                    'name' => $item->name,
                    'symbol' => $item->symbol,
                    'price_usd' => $item->price->usd,
                    'percent_change' => $item->percent_change->h24,
                    'change_usd' => $item->change_usd
                ]);
            }, array_slice(array_reverse($sortedChanges), 0, 3)),
            'fall' => array_map(function (Entities\Currency $item): Entities\TopData {
                return new Entities\TopData([
                    'name' => $item->name,
                    'symbol' => $item->symbol,
                    'price_usd' => $item->price->usd,
                    'percent_change' => $item->percent_change->h24,
                    'change_usd' => $item->change_usd
                ]);
            }, array_slice($sortedChanges, 0, 3))
        ];

        return $result;
    }

    public function getUpdateTime(): ?string
    {
        return $this->cache->get($this->buildCacheKey(CacheKey::UPDATE_TIME));
    }

    protected function getChangesSorted(array $currencyList): array
    {
        usort($currencyList, function (Entities\Currency $item1, Entities\Currency $item2): int {
            $value1 = number_format($item1->change_usd, 10);
            $value2 = number_format($item2->change_usd, 10);

            if ($value1 > $value2) {
                return 1;
            }

            return $value1 === $value2 ? 0 : -1;
        });

        return $currencyList;
    }

    protected function buildCacheKey(string $action): string
    {
        return "crypto-currencies-repository.{$action}";
    }
}
