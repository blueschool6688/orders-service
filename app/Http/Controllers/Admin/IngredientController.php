<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\IngredientRequest;
use App\Http\Requests\PaginateRequest;
use App\Http\Resources\IngredientResource;
use App\Models\Ingredient;
use Illuminate\Http\Request;
use App\services\IngredientService;
class IngredientController extends AdminController
{
    public IngredientService $ingredientService;
    public function __construct(IngredientService $ingredientService){
        parent::__construct();
        $this->ingredientService = $ingredientService;
    }
    public function index(PaginateRequest $request){
        try{
            return IngredientResource::collection($this->ingredientService->list($request));
        } catch (\Exception $exception){
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }
    public function store(IngredientRequest $request)
    {
        try{
            return new IngredientResource($this->ingredientService->store($request));
        }catch (\Exception $exception){
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }
    public function update(IngredientRequest $request, Ingredient $ingredient){
        try {
            return new IngredientResource($this->ingredientService->update($request, $ingredient));
        }catch (\Exception $exception){
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }
    public function destroy(Ingredient $ingredient)
    {
        try {
            $this->ingredientService->destroy($ingredient);
        }catch (\Exception $exception){
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }
}
