<?php

namespace Wearesho\CryptoCurrency;

/**
 * Interface RepositoryInterface
 * @package Wearesho\CryptoCurrency
 */
interface RepositoryInterface
{
    public function pullCurrency(): array;
    public function pullGlobal(): Entities\GlobalData;
    public function pullTops(): array;
}
