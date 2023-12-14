<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Helpers\MyResponse;
use App\Http\Requests\Goals\CreateGoalRequest;
use App\Http\Requests\Goals\ReturnToWalletRequest;
use App\Http\Requests\Goals\TransferToGoalRequest;
use App\Http\Requests\Goals\UpdateGoalRequest;
use App\Http\Services\GoalService;
use Illuminate\Http\Request;

class GoalsController extends Controller
{
    public function __construct(private GoalService $goalService)
    {
    }

    public function create(CreateGoalRequest $request)
    {
        $resultData = $this->goalService->create($request->user(), $request->all());

        return (new MyResponse($resultData))->get();
    }

    public function get(Request $request)
    {
        $resultData = $this->goalService->get($request->user(), $request->all());

        return (new MyResponse($resultData))->get();
    }

    public function getTransferable(Request $request)
    {
        $resultData = $this->goalService->getTransferable($request->user(), $request->all());

        return (new MyResponse($resultData))->get();
    }

    public function transferToAnotherGoal(TransferToGoalRequest $request, int $id)
    {
        $resultData = $this->goalService->transferToAnotherGoal($id, $request->all());

        return (new MyResponse($resultData))->get();
    }

    public function returnBackToWallet(ReturnToWalletRequest $request, int $id)
    {
        $resultData = $this->goalService->returnBackToWallet($id, $request->all());

        return (new MyResponse($resultData))->get();
    }

    public function update(UpdateGoalRequest $request, int $id)
    {
        $resultData = $this->goalService->update($request->user(), $id, $request->all());

        return (new MyResponse($resultData))->get();
    }

    public function delete(Request $request, int $id)
    {
        $resultData = $this->goalService->delete($id);

        return (new MyResponse($resultData))->get();
    }
}
