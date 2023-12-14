<?php

namespace Tests\Unit\Requests;

use App\Http\Requests\Plans\CreateMonthPlanRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class CreateMonthPlanRequestTest extends TestCase
{
    use RefreshDatabase;

    private $user;

    private $createMonthPlanRequest;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createMonthPlanRequest = new CreateMonthPlanRequest();
    }

    public function test_authorize()
    {
        $this->assertTrue(true);
    }

    public function test_rules()
    {
        $data = [
            'month' => random_int(1, 12),
            'year' => random_int(2018, 2028),
            'amount' => random_int(1, 1000000),
            'wallet_id' => random_int(1, 100),
        ];

        $validator = Validator::make($data, $this->createMonthPlanRequest->rules());

        $this->assertFalse($validator->fails());
    }
}
