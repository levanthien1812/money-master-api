<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Helpers\MyResponse;
use App\Http\Requests\Users\UpdatePasswordRequest;
use App\Http\Requests\Users\UpdateUserRequest;
use App\Http\Services\UserServices;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct(private UserServices $userServices)
    {
    }

    public function updateUser(UpdateUserRequest $request)
    {
        $returnData = $this->userServices->updateUser($request->user(), $request->validated());

        return (new MyResponse($returnData))->get();
    }

    public function updatePassword(UpdatePasswordRequest $request)
    {
        $returnData = $this->userServices->updatePassword($request->user()->id, $request->validated());

        return (new MyResponse($returnData))->get();
    }

    public function deleteUser(Request $request)
    {
        $returnData = $this->userServices->delete($request->user()->id);

        return (new MyResponse($returnData))->get();
    }

    public function deleteById(Request $request, int $id)
    {
        $returnData = $this->userServices->delete($id);

        return (new MyResponse($returnData))->get();
    }

    public function getAll(Request $request)
    {
        $returnData = $this->userServices->getUsers($request->all());

        return (new MyResponse($returnData))->get();
    }

    public function getCounts(Request $request)
    {
        $returnData = $this->userServices->countUsers();

        return (new MyResponse($returnData))->get();
    }

    public function getYears(Request $request)
    {
        $returnData = $this->userServices->getYears();

        return (new MyResponse($returnData))->get();
    }
}
