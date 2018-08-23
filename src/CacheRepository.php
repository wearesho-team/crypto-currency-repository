<?php

namespace Wearesho\CryptoCurrency;

use Psr\SimpleCache\CacheInterface;
use yii\queue\Queue;

/**
 * Class ProxyRepository
 * @package Wearesho\CryptoCurrency
 */
class CacheRepository implements RepositoryInterface
{
    /** @var Queue */
    protected $queue;

    /** @var CacheInterface */
    protected $cache;

    /** @var RepositoryInterface */
    protected $repository;

    public function __construct(
        Queue $queue,
        CacheInterface $cache,
        RepositoryInterface $repository,
        array $config = []
    ) {
        $this->cache = $cache;
        $this->queue = $queue;
        $this->repository = $repository;
    }

    public function pullCurrency(): array
    {
        $cacheKey = $this->buildCacheKey(Action::CURRENCY);

        $cachedValue = $this->cache->get($cacheKey);
        if (is_array($cachedValue)) {
            return $cachedValue;
        }

        $response = $this->repository->pullCurrency();

        $this->cache->set($cacheKey, $response, 60 * 60 * 2);
        $this->queue
            ->delay(60 * 60)
            ->push(new Jobs\UpdateData(['actions' => Action::CURRENCY]));

        return $response;
    }

    public function pullGlobal(): Entities\GlobalData
    {
        $cacheKey = $this->buildCacheKey(Action::GLOBAL_DATA);

        $cachedValue = $this->cache->get($cacheKey);
        if ($cachedValue) {
            return $cachedValue;
        }

        $response = $this->repository->pullGlobal();

        $this->cache->set($cacheKey, $response, 60 * 60 * 2);
        $this->queue
            ->delay(60 * 60)
            ->push(new Jobs\UpdateData(['actions' => Action::GLOBAL_DATA]));

        return $response;
    }

    public function pullTops(): array
    {
        return $this->repository->pullTops();
    }

    protected function buildCacheKey(string $action): string
    {
        return "crypto-currencies-repository.{$action}";
    }
}
