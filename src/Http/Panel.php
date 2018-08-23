<?php

namespace Wearesho\CryptoCurrency\Http;

use Wearesho\CryptoCurrency\CacheRepository;
use Wearesho\CryptoCurrency\Repository;
use Wearesho\Yii\Http;

/**
 * Class Panel
 * @package Wearesho\CryptoCurrency\Http
 */
class Panel extends Http\Panel
{
    /** @var Repository */
    protected $repository;

    public function __construct(
        Http\Request $request,
        Http\Response $response,
        CacheRepository $repository,
        array $config = []
    ) {
        parent::__construct($request, $response, $config);
        $this->repository = $repository;
    }

    /**
     * @inheritdoc
     */
    protected function generateResponse(): array
    {
        $currencies = $this->repository->pullCurrency();

        return [
            'currencies' => $currencies,
            'global' => $this->repository->pullGlobal(),
            'tops' => $this->repository->pullTops($currencies),
        ];
    }
}
