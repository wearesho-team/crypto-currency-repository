<?php

namespace Wearesho\CryptoCurrency;

use GuzzleHttp\ClientInterface;
use yii\base;

/**
 * Class Repository
 * @package Wearesho\CryptoCurrency
 */
class Repository extends base\BaseObject implements RepositoryInterface
{
    protected const BASE_URI = 'https://api.coinmarketcap.com/v1/';

    /** @var ClientInterface */
    protected $client;

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

    public function __construct(ClientInterface $client, array $config = [])
    {
        parent::__construct($config);
        $this->client = $client;
    }

    public function pullCurrency(): array
    {
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

        return array_map(function (array $item_uah, array $item_btc): Entities\Currency {
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
    }

    public function pullGlobal(): Entities\GlobalData
    {
        $global = json_decode(
            $this->client->request('GET', self::BASE_URI . 'global/')
                ->getBody()
                ->getContents(),
            true
        );

        return new Entities\GlobalData([
            'totalMarketCap' => $global['total_market_cap_usd'],
            'totalVolume' => $global['total_24h_volume_usd']
        ]);
    }

    public function pullTops(): array
    {
        $sortedChanges = $this->getChangesSorted();

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

    protected function getChangesSorted(): array
    {
        $currencyList = $this->pullCurrency();

        usort($currencyList, function (Entities\Currency $item1, Entities\Currency $item2): int {
            if ($item1->change_usd > $item2->change_usd) {
                return $item1->change_usd == $item2->change_usd ? 0 : 1;
            }
            return -1;
        });

        return $currencyList;
    }

}
