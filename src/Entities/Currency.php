<?php

namespace Wearesho\CryptoCurrency\Entities;

use yii\base;
use Wearesho\CryptoCurrency\Containers\ChangeContainer;
use Wearesho\CryptoCurrency\Containers\PriceContainer;

/**
 * Class Currency
 * @package Wearesho\CryptoCurrency\Entities
 */
class Currency extends base\BaseObject
{
    /** @var string */
    public $id;

    /** @var string */
    public $name;

    /** @var string */
    public $symbol;

    /** @var float */
    public $available_supply;

    /** @var float */
    public $change_usd;

    /** @var PriceContainer */
    public $price;

    /** @var PriceContainer */
    public $market_cap;

    /** @var PriceContainer */
    public $volume;

    /** @var ChangeContainer */
    public $percent_change;
}
