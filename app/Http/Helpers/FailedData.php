<?php

namespace App\Http\Helpers;

class FailedData extends BaseData
{
    public array $error;

    public function __construct(
        string $message,
        array $error = null,
    ) {
        parent::__construct($message, 'failed');

        if (! $error) {
            $this->error = ['error' => $message];
        } else {
            $this->error = $error;
        }
    }

    public function get(): array
    {
        return ['message' => $this->message, 'error' => $this->error, 'status' => $this->status];
    }

    public function getError(): array
    {
        return $this->error;
    }
}
