<?php

namespace Wearesho\CryptoCurrency\Entities;

use yii\base;

/**
 * Class GlobalData
 * @package Wearesho\CryptoCurrency\Entities
 */
class GlobalData extends base\BaseObject
{
    /** @var float */
    public $totalMarketCap;

    /** @var float */
    public $totalVolume;
}
