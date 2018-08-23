<?php

namespace Wearesho\CryptoCurrency;

use GuzzleHttp\ClientInterface;
use Psr\SimpleCache\CacheInterface;
use yii\queue\Queue;

/**
 * Class ProxyRepository
 * @package Wearesho\CryptoCurrency
 */
class ProxyRepository extends Repository
{
    /** @var Queue */
    protected $queue;

    /** @var CacheInterface */
    protected $cache;

    public function __construct(Queue $queue, CacheInterface $cache, ClientInterface $client, array $config = [])
    {
        parent::__construct($client, $config);
        $this->cache = $cache;
        $this->client = $client;
        $this->queue = $queue;
    }

    public function pullCurrency($forceUpdate = false): array
    {
        $cacheKey = $this->buildCacheKey(Action::CURRENCY);

        $cachedValue = $this->cache->get($cacheKey);
        if (is_array($cachedValue)) {
            return $cachedValue;
        }

        $response = parent::pullCurrency($forceUpdate);

        $this->cache->set($cacheKey, $response, 60 * 60 * 2);
        $this->queue
            ->delay(60 * 60)
            ->push(new Jobs\UpdateData(['actions' => Action::CURRENCY]));

        return $response;
    }

    public function pullGlobal($forceUpdate = false): Entities\GlobalData
    {
        $cacheKey = $this->buildCacheKey(Action::GLOBAL_DATA);

        $cachedValue = $this->cache->get($cacheKey);
        if ($cachedValue) {
            return $cachedValue;
        }

        $response = parent::pullGlobal($forceUpdate);

        $this->cache->set($cacheKey, $response, 60 * 60 * 2);
        $this->queue
            ->delay(60 * 60)
            ->push(new Jobs\UpdateData(['actions' => Action::GLOBAL_DATA]));

        return $response;
    }

    protected function buildCacheKey(string $action): string
    {
        return "crypto-currencies-repository.{$action}";
    }
}
