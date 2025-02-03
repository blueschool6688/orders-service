<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\PaginateRequest;
use App\Http\Requests\TopUpPackageRequest;
use App\Models\TopUpPackage;
use App\Services\TopUpPackageService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Resources\TopUpPackageResource;
class TopUpPackageController extends AdminController
{
    private TopUpPackageService $topUpPackageService;

    public function __construct(TopUpPackageService $topUpPackageService){
        parent::__construct();
        $this->topUpPackageService = $topUpPackageService;
        $this->middleware(['permission:settings'])->only('store', 'update', 'destroy', 'show');
    }
    public function index(PaginateRequest $request)
    {
        try {
            return TopUpPackageResource::collection($this->topUpPackageService->list($request));
        } catch (\Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }
    public function store(TopUpPackageRequest $request)
    {
        try{
            return new TopUpPackageResource($this->topUpPackageService->store($request));
        }catch (\Exception $exception){
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }
    public function update(TopUpPackageRequest $request, TopUpPackage $topupPackage): \Illuminate\Http\Response | TopUpPackageResource
    {
        try {
            return new TopUpPackageResource($this->topUpPackageService->update($request, $topupPackage));
        } catch (\Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function destroy(TopUpPackage $topupPackage)
    {
        try {
            $this->topUpPackageService->destroy($topupPackage);
            return response('', 202);
        }catch (\Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }
}
