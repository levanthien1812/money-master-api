<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Helpers\MyResponse;
use App\Http\Services\ReportService;
use Illuminate\Http\Request;

class ReportsController extends Controller
{
    public function __construct(private ReportService $reportService)
    {
    }

    public function get(Request $request)
    {
        $returnData = $this->reportService->get($request->user(), $request->all());

        return (new MyResponse($returnData))->get();
    }

    public function getUserQuantityPerMonth(Request $request)
    {
        $returnData = $this->reportService->getUserQuantityPerMonth($request->all());

        return (new MyResponse($returnData))->get();
    }

    public function getTransactionQuantityPerMonth(Request $request)
    {
        $returnData = $this->reportService->getTransactionQuantityPerMonth($request->all());

        return (new MyResponse($returnData))->get();
    }

    public function export(Request $request)
    {
        return $this->reportService->export($request->user(), $request->all());
    }
}
