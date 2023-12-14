<?php

namespace Tests\Unit\Requests;

use App\Http\Requests\Plans\UpdatePlanRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class UpdatePlanRequestTest extends TestCase
{
    use RefreshDatabase;

    private $user;

    private $updatePlanRequest;

    protected function setUp(): void
    {
        parent::setUp();
        $this->updatePlanRequest = new UpdatePlanRequest();
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
            'category_id' => random_int(1, 100),
            'wallet_id' => random_int(1, 100),
        ];

        $validator = Validator::make($data, $this->updatePlanRequest->rules());

        $this->assertFalse($validator->fails());
    }
}
