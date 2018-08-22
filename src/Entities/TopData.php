<?php

namespace Wearesho\CryptoCurrency\Entities;

use yii\base;

/**
 * Class TopEntity
 * @package Wearesho\CryptoCurrency\Entities
 */
class TopData extends base\BaseObject
{
    /** @var string */
    public $name;

    /** @var string */
    public $symbol;

    /** @var float */
    public $price_usd;

    /** @var float */
    public $percent_change;

    /** @var float */
    public $change_usd;
}
