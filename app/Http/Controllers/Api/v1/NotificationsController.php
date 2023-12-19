<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Helpers\MyResponse;
use App\Http\Services\NotificationService;
use Illuminate\Http\Request;

class NotificationsController extends Controller
{
    //
    public function __construct(private NotificationService $notificationService)
    {
    }

    public function get(Request $request)
    {
        $resultData = $this->notificationService->get($request->user());

        return (new MyResponse($resultData))->get();
    }

    public function markAsRead(Request $request, string $id)
    {
        $resultData = $this->notificationService->markAsRead($request->user(), $id);

        return (new MyResponse($resultData))->get();
    }

    public function markAllAsRead(Request $request)
    {
        $resultData = $this->notificationService->markAllAsRead($request->user());

        return (new MyResponse($resultData))->get();
    }

    public function delete(Request $request, string $id)
    {
        $resultData = $this->notificationService->delete($request->user(), $id);

        return (new MyResponse($resultData))->get();
    }
}
