<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Helpers\MyResponse;
use App\Http\Requests\Categories\CreateCategoryRequest;
use App\Http\Requests\Categories\UpdateCategoryRequest;
use App\Http\Services\CategoryServices;
use Illuminate\Http\Request;

class CategoriesController extends Controller
{
    public function __construct(private CategoryServices $categoryServices)
    {
    }

    public function create(CreateCategoryRequest $request)
    {
        $returnData = $this->categoryServices->create($request->user(), $request->validated());

        return (new MyResponse($returnData))->get();
    }

    public function get(Request $request)
    {
        $returnData = $this->categoryServices->get($request->user(), $request->all());

        return (new MyResponse($returnData))->get();
    }

    public function getDefault(Request $request)
    {
        $returnData = $this->categoryServices->getDefault($request->all());

        return (new MyResponse($returnData))->get();
    }

    public function getDefaultCount(Request $request)
    {
        $returnData = $this->categoryServices->getDefaultCount();

        return (new MyResponse($returnData))->get();
    }

    public function update(UpdateCategoryRequest $request, int $id)
    {
        $returnData = $this->categoryServices->update($request->validated(), $id);

        return (new MyResponse($returnData))->get();
    }

    public function delete(Request $request, int $id)
    {
        $returnData = $this->categoryServices->delete($id);

        return (new MyResponse($returnData))->get();
    }

    public function deleteDefault(Request $request, int $id)
    {
        $returnData = $this->categoryServices->deleteDefault($id);

        return (new MyResponse($returnData))->get();
    }
}
