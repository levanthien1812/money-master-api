<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Helpers\MyResponse;
use App\Http\Requests\Plans\CreateMonthPlanRequest;
use App\Http\Requests\Plans\UpdatePlanRequest;
use App\Http\Services\MonthPlanService;
use Illuminate\Http\Request;

class MonthPlansController extends Controller
{
    public function __construct(private MonthPlanService $monthPlanService)
    {
    }

    public function create(CreateMonthPlanRequest $request)
    {
        $returnData = $this->monthPlanService->create($request->user(), $request->validated());

        return (new MyResponse($returnData))->get();
    }

    public function get(Request $request)
    {
        $returnData = $this->monthPlanService->get($request->user(), $request->all());

        return (new MyResponse($returnData))->get();
    }

    public function update(UpdatePlanRequest $request, int $id)
    {
        $returnData = $this->monthPlanService->update($request->user(), $request->validated(), $id);

        return (new MyResponse($returnData))->get();
    }

    public function delete(Request $request, int $id)
    {
        $returnData = $this->monthPlanService->delete($id);

        return (new MyResponse($returnData))->get();
    }

    public function getYears(Request $request)
    {
        $returnData = $this->monthPlanService->getYears($request->user(), $request->all());

        return (new MyResponse($returnData))->get();
    }
}
