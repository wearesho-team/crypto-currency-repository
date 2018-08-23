<?php

namespace Wearesho\CryptoCurrency\Repository;

use Wearesho\CryptoCurrency;
use yii\base;

/**
 * Class Bootstrap
 * @package Wearesho\CryptoCurrency
 */
class Bootstrap extends base\BaseObject implements base\BootstrapInterface
{
    /** @var string[] */
    public $allowedCurrencies = [
        CryptoCurrency\Currency::bitcoin,
        CryptoCurrency\Currency::litecoin,
        CryptoCurrency\Currency::tether,
        CryptoCurrency\Currency::monero,
        CryptoCurrency\Currency::ripple,
        CryptoCurrency\Currency::kickico,
        CryptoCurrency\Currency::zcash,
        CryptoCurrency\Currency::waves,
        CryptoCurrency\Currency::ethereumClassic,
        CryptoCurrency\Currency::ethereum,
        CryptoCurrency\Currency::dash,
        CryptoCurrency\Currency::dogecoin,
        CryptoCurrency\Currency::bitcoinCash,
    ];

    /**
     * @inheritdoc
     */
    public function bootstrap($app)
    {
        \Yii::$container->set(CryptoCurrency\RepositoryInterface::class, [
            'class' => CryptoCurrency\Repository::class,
            'allowedCurrencies' => $this->allowedCurrencies,
        ]);
    }
}
