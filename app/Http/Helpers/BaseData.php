<?php

namespace App\Http\Helpers;

abstract class BaseData
{
    public function __construct(
        public string $message,
        public string $status
    ) {
    }

    abstract public function get(): array;
}
