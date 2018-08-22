<?php

namespace Wearesho\CryptoCurrency\Http;

use Horat1us\Yii\Validators\ConstRangeValidator;
use Wearesho\CryptoCurrency\Action;
use Wearesho\CryptoCurrency\Jobs\UpdateData;
use Wearesho\Yii\Http;
use yii\db;
use yii\queue;

/**
 * Class UpdateForm
 * @package Wearesho\CryptoCurrency\Http
 */
class UpdateForm extends Http\Form
{
    /** @var array */
    public $actions;

    /** @var queue\Queue */
    protected $queue;

    public function __construct(
        Http\Request $request,
        Http\Response $response,
        db\Connection $connection,
        queue\Queue $queue,
        array $config = []
    ) {
        parent::__construct($request, $response, $connection, $config);
        $this->queue = $queue;
    }

    public function rules(): array
    {
        return [
            [
                ['actions',],
                'required',
            ],
            [
                ['actions',],
                'each',
                'rule' => [
                    ConstRangeValidator::class,
                    'targetClass' => Action::class,
                    'prefix' => '',
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    protected function generateResponse(): array
    {
        $this->queue->push(
            new UpdateData([
                'actions' => $this->actions,
            ])
        );

        $this->response->statusCode = 202;

        return [];
    }
}
