<?php

namespace App\Http\Helpers;

class MyResponse
{
    public int $httpCode;

    public BaseData $data;

    public function __construct(
        BaseData $data,
    ) {
        if (! isset($httpCode)) {
            if ($data instanceof SuccessfulData) {
                $this->httpCode = 200;
            } else {
                $this->httpCode = 404;
            }
        } else {
            $this->httpCode = $httpCode;
        }

        $this->data = $data;
    }

    public function get()
    {
        return response()->json($this->data->get(), $this->httpCode);
    }
}
