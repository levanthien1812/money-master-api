<?php

namespace App\Http\Helpers;

class SuccessfulData extends BaseData
{
    public array $data;

    public function __construct(
        string $message,
        array $data = [],
    ) {
        parent::__construct($message, 'success');
        $this->data = $data;
    }

    public function get(): array
    {
        return ['message' => $this->message, 'data' => $this->data, 'status' => $this->status];
    }

    public function getData(): array
    {
        return $this->data;
    }
}
