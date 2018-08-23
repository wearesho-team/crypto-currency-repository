<?php

namespace Wearesho\CryptoCurrency\Jobs;

use Wearesho\CryptoCurrency\Action;
use Wearesho\CryptoCurrency\Repository;
use yii\base;
use yii\queue;

/**
 * Class UpdateData
 * @package Wearesho\CryptoCurrency\Jobs
 */
class UpdateData extends base\BaseObject implements queue\JobInterface
{
    /** @var string|string[] */
    public $actions = [
        Action::CURRENCY,
        Action::GLOBAL_DATA,
        Action::TOP_DATA,
    ];

    /**
     * @inheritdoc
     */
    public function execute($queue): void
    {
        /** @var Repository $repository */
        $repository = \Yii::$container->get(Repository::class);

        foreach ((array)$this->actions as $action) {
            switch ($action) {
                case Action::CURRENCY:
                    $repository->pullCurrency(true);
                    break;
                case Action::GLOBAL_DATA:
                    $repository->pullGlobal(true);
            }
        }
    }
}
