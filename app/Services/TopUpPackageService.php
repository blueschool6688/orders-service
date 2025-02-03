<?php

namespace App\Services;
use App\Http\Requests\TopUpPackageRequest;
use Exception;

use App\Models\TopUpPackage;
use Illuminate\Support\Str;
use App\Http\Requests\PageRequest;
use App\Http\Requests\PaginateRequest;
use Illuminate\Support\Facades\Log;

class TopUpPackageService
{

    /**
     * @throws Exception
     */
    public function list(PaginateRequest $request){
        try{
            $requests = $request->all();
            $method      = $request->get('paginate', 0) == 1 ? 'paginate' : 'get';
            $methodValue = $request->get('paginate', 0) == 1 ? $request->get('per_page', 10) : '*';
            $orderColumn = $request->get('order_column') ?? 'id';
            $orderType   = $request->get('order_type') ?? 'desc';

            return TopUpPackage::orderBy($orderColumn, $orderType)->$method(
                $methodValue
            );
        }catch (Exception $e){
            Log::info($e->getMessage());
            throw new Exception($e->getMessage(), 422);
        }
    }
    public function store(TopUpPackageRequest $request){
        try {
            $topupPackage = TopUpPackage::create($request->validated());
            return $topupPackage;
        }catch (Exception $exception){
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }

    }
    public function update(TopUpPackageRequest $request, TopUpPackage $topupPackage)
    {
        try{
            $topupPackage->update($request->validated());
            return $topupPackage;
        }
        catch (Exception $exception){
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function destroy(TopUpPackage $topupPackage)
    {
        try{
            $topupPackage->delete();
        }catch (Exception $exception){
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }
}
