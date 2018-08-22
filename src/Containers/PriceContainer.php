<?php

namespace Wearesho\CryptoCurrency\Containers;

use yii\base;

/**
 * Class PriceContainer
 * @package Wearesho\CryptoCurrency\Containers
 */
class PriceContainer extends base\BaseObject
{
    /** @var float */
    public $uah;

    /** @var float */
    public $usd;

    /** @var float */
    public $btc;
}
