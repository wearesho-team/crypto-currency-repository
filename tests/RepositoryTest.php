<?php

namespace Wearesho\CryptoCurrency\Tests;

use Cache\Adapter\PHPArray\ArrayCachePool;
use GuzzleHttp;
use PHPUnit\Framework\TestCase;
use Wearesho\CryptoCurrency\Repository;

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

    protected function setUp(): void
    {
        parent::setUp();
        $this->mock = new GuzzleHttp\Handler\MockHandler();
        $container = [];
        $history = GuzzleHttp\Middleware::history($container);
        $stack = new GuzzleHttp\HandlerStack($this->mock);
        $stack->push($history);
        $this->client = new GuzzleHttp\Client(['handler' => $stack,]);
    }

    public function testPullCurrency(): void
    {
        $this->mock->append(
            new GuzzleHttp\Psr7\Response(200, [], file_get_contents(\Yii::getAlias('@tests/input/uah_currency.json'))),
            new GuzzleHttp\Psr7\Response(200, [], file_get_contents(\Yii::getAlias('@tests/input/btc_currency.json')))
        );

        $repository = new Repository(
            new ArrayCachePool(),
            $this->client
        );
        $resp = $repository->pullCurrency();

        $this->assertObjectHasAttribute("name", $resp[1]);
        $this->assertObjectHasAttribute("market_cap", $resp[1]);
        $this->assertObjectHasAttribute("usd", $resp[1]->market_cap);

        $this->assertEquals('6669.60114187', $resp[0]->price->usd);
        $this->assertEquals('3198386835725', $resp[0]->market_cap->uah);
        $this->assertEquals('3.59', $resp[0]->percent_change->h24);

        $this->assertEquals('3.14', $resp[2]->percent_change->h24);
        $this->assertEquals('0.00005098', $resp[2]->price->btc);
        $this->assertEquals('XRP', $resp[2]->symbol);
    }

    public function testPullGlobal(): void
    {
        $this->mock->append(
            new GuzzleHttp\Psr7\Response(200, [], file_get_contents(\Yii::getAlias('@tests/input/global.json')))
        );

        $repository = new Repository(
            new ArrayCachePool(),
            $this->client
        );
        $resp = $repository->pullGlobal();

        $this->assertEquals(12023220573, $resp->totalVolume);
        $this->assertEquals(209106698893, $resp->totalMarketCap);
    }

    public function testPullTops(): void
    {
        $this->mock->append(
            new GuzzleHttp\Psr7\Response(200, [], file_get_contents(\Yii::getAlias('@tests/input/uah_currency.json'))),
            new GuzzleHttp\Psr7\Response(200, [], file_get_contents(\Yii::getAlias('@tests/input/btc_currency.json')))
        );

        $repository = new Repository(
            new ArrayCachePool(),
            $this->client
        );
        $resp = $repository->pullTops();

        $this->assertArrayHasKey("grow", $resp);
        $this->assertObjectHasAttribute("price_usd", $resp["fall"][0]);

        $this->assertEquals("BTC", $resp["grow"][0]->symbol);
        $this->assertEquals(547.69087298, $resp["grow"][1]->price_usd);
        $this->assertEquals("Ethereum", $resp["grow"][2]->name);

        $this->assertEquals("KICK", $resp["fall"][0]->symbol);
        $this->assertEquals(1.0011140715, $resp["fall"][1]->price_usd);
        $this->assertEquals("Dogecoin", $resp["fall"][2]->name);
    }

    public function testRealRequest(): void
    {
        $repository = new Repository(
            $cache = new ArrayCachePool(),
            new GuzzleHttp\Client
        );
        $response = $repository->pullTops();
        $this->assertArrayHasKey('grow', $response);
        $this->assertArrayHasKey('fall', $response);
        $cachedValue = $cache->get("crypto-currencies-repository.topData");
        $this->assertEquals($response, $cachedValue);
    }
}
