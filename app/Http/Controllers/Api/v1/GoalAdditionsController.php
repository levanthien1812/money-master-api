<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Helpers\MyResponse;
use App\Http\Requests\GoalAdditions\CreateGoalAdditionRequest;
use App\Http\Services\GoalAdditionService;
use Illuminate\Http\Request;

class GoalAdditionsController extends Controller
{
    public function __construct(private GoalAdditionService $goalAdditionService)
    {
    }

    public function create(CreateGoalAdditionRequest $request, int $goalId)
    {
        $resultData = $this->goalAdditionService->create($goalId, $request->all());

        return (new MyResponse($resultData))->get();
    }

    public function get(Request $request, int $goalId)
    {
        $resultData = $this->goalAdditionService->getByGoalId($goalId);

        return (new MyResponse($resultData))->get();
    }
}
