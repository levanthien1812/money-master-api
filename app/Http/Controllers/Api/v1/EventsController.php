<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Helpers\MyResponse;
use App\Http\Requests\Events\CreateEventRequest;
use App\Http\Services\EventService;
use Illuminate\Http\Request;

class EventsController extends Controller
{
    public function __construct(private EventService $eventServices)
    {
    }

    public function create(CreateEventRequest $request)
    {
        $resultData = $this->eventServices->create($request->user(), $request->all());

        return (new MyResponse($resultData))->get();
    }

    public function get(Request $request)
    {
        $resultData = $this->eventServices->get($request->user(), $request->all());

        return (new MyResponse($resultData))->get();
    }

    public function update(Request $request, int $id)
    {
        $resultData = $this->eventServices->update($request->all(), $id);

        return (new MyResponse($resultData))->get();
    }

    public function deleleWithTransactions(Request $request, int $id)
    {
        $resultData = $this->eventServices->deleteWithTransactions($id);

        return (new MyResponse($resultData))->get();
    }

    public function deleteWithoutTransactions(Request $request, int $id)
    {
        $resultData = $this->eventServices->deleteWithoutTransactions($id);

        return (new MyResponse($resultData))->get();
    }

    public function getTransactions(Request $request, int $id)
    {
        $resultData = $this->eventServices->getTransactions($id);
        return (new MyResponse($resultData))->get();
    }
}
