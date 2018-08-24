<?php

namespace Wearesho\CryptoCurrency\Tests;

use Cache\Adapter\PHPArray\ArrayCachePool;
use GuzzleHttp;
use PHPUnit\Framework\TestCase;
use Psr\SimpleCache\CacheInterface;
use Wearesho\CryptoCurrency\Repository;
use Wearesho\CryptoCurrency\Currency;
use yii\queue\file\Queue;

/**
 * Class RepositoryTest
 * @package Wearesho\CryptoCurrency\Tests
 */
class RepositoryTest extends TestCase
{
    /** @var GuzzleHttp\Handler\MockHandler */
    protected $mock;

    /** @var GuzzleHttp\Client */
    protected $client;

    /** @var array */
    protected $container;

    /** @var CacheInterface */
    protected $cache;

    /** @var Repository */
    protected $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mock = new GuzzleHttp\Handler\MockHandler();
        $this->container = [];
        $history = GuzzleHttp\Middleware::history($this->container);
        $stack = new GuzzleHttp\HandlerStack($this->mock);
        $stack->push($history);
        $this->client = new GuzzleHttp\Client(['handler' => $stack,]);
        $this->cache = new ArrayCachePool();
        $this->repository = new Repository($this->cache, $this->client);
        $this->repository->allowedCurrencies = [
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
    }

    public function testPullCurrency(): void
    {
        $this->mock->append(
            new GuzzleHttp\Psr7\Response(200, [], file_get_contents(\Yii::getAlias('@tests/input/uah_currency.json'))),
            new GuzzleHttp\Psr7\Response(200, [], file_get_contents(\Yii::getAlias('@tests/input/btc_currency.json')))
        );

        $resp = $this->repository->pullCurrency();

        $this->assertObjectHasAttribute("name", $resp[1]);
        $this->assertObjectHasAttribute("market_cap", $resp[1]);
        $this->assertObjectHasAttribute("usd", $resp[1]->market_cap);

        $this->assertEquals('6669.60114187', $resp[0]->price->usd);
        $this->assertEquals('3198386835725', $resp[0]->market_cap->uah);
        $this->assertEquals('3.59', $resp[0]->percent_change->h24);

        $this->assertEquals('3.14', $resp[2]->percent_change->h24);
        $this->assertEquals('0.00005098', $resp[2]->price->btc);
        $this->assertEquals('XRP', $resp[2]->symbol);

        $cachedValue = $this->cache->get('crypto-currencies-repository.currency');
        $this->assertEquals($resp, $cachedValue);
    }

    public function testPullGlobal(): void
    {
        $this->mock->append(
            new GuzzleHttp\Psr7\Response(200, [], file_get_contents(\Yii::getAlias('@tests/input/global.json')))
        );

        $resp = $this->repository->pullGlobal();

        $this->assertEquals(12023220573, $resp->totalVolume);
        $this->assertEquals(209106698893, $resp->totalMarketCap);

        $cachedValue = $this->cache->get('crypto-currencies-repository.globalData');
        $this->assertEquals($resp, $cachedValue);
    }

    public function testPullTops(): void
    {
        $this->mock->append(
            new GuzzleHttp\Psr7\Response(200, [], file_get_contents(\Yii::getAlias('@tests/input/uah_currency.json'))),
            new GuzzleHttp\Psr7\Response(200, [], file_get_contents(\Yii::getAlias('@tests/input/btc_currency.json')))
        );

        $resp = $this->repository->generateTops($this->repository->pullCurrency());

        $this->assertArrayHasKey("grow", $resp);
        $this->assertObjectHasAttribute("price_usd", $resp["fall"][0]);

        $this->assertEquals("BTC", $resp["grow"][0]->symbol);
        $this->assertEquals(547.69087298, $resp["grow"][1]->price_usd);
        $this->assertEquals("Ethereum", $resp["grow"][2]->name);

        $this->assertEquals("KICK", $resp["fall"][0]->symbol);
        $this->assertEquals(1.0011140715, $resp["fall"][1]->price_usd);
        $this->assertEquals("Dogecoin", $resp["fall"][2]->name);
    }

    public function testUpdateTime(): void
    {
        $time = (new \DateTime())->format('Y-m-d H:i:s');
        $this->assertGreaterThanOrEqual($time, $this->repository->getUpdateTime());

        $this->mock->append(
            new GuzzleHttp\Psr7\Response(200, [], file_get_contents(\Yii::getAlias('@tests/input/global.json')))
        );

        $this->repository->pullGlobal();

        $this->assertTrue($this->cache->has('crypto-currencies-repository.updateTime'));
    }
}
