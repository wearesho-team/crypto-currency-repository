<?php

namespace Wearesho\CryptoCurrency;

use yii\base;

/**
 * Class Bootstrap
 * @package Wearesho\CryptoCurrency
 */
class Bootstrap extends base\BaseObject implements base\BootstrapInterface
{
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

    /**
     * @inheritdoc
     */
    public function bootstrap($app)
    {
        \Yii::$container->set(RepositoryInterface::class, [
            'class' => Repository::class,
            'allowedCurrencies' => $this->allowedCurrencies,
        ]);
    }
}
