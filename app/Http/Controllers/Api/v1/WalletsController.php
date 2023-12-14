<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Helpers\MyResponse;
use App\Http\Requests\Wallets\CreateWalletRequest;
use App\Http\Requests\Wallets\UpdateWalletRequest;
use App\Http\Services\WalletServices;
use Illuminate\Http\Request;

class WalletsController extends Controller
{
    public function __construct(private WalletServices $walletServices)
    {
    }

    public function create(CreateWalletRequest $request)
    {
        $returnData = $this->walletServices->create($request->user(), $request->all());

        return (new MyResponse($returnData))->get();
    }

    public function get(Request $request)
    {
        $returnData = $this->walletServices->get($request->user());

        return (new MyResponse($returnData))->get();
    }

    public function update(UpdateWalletRequest $request, int $id)
    {
        $returnData = $this->walletServices->update($request->all(), $id);

        return (new MyResponse($returnData))->get();
    }

    public function delete(Request $request, int $id)
    {
        $returnData = $this->walletServices->delete($id);

        return (new MyResponse($returnData))->get();
    }
}
