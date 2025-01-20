<?php

namespace App\services;
use App\Http\Requests\IngredientRequest;
use App\Models\IngredientLog;
use Exception;
use App\Models\Ingredient;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\PaginateRequest;
use Smartisan\Settings\Facades\Settings;

class IngredientService{
    public $ingredient;
    protected $ingredientFilter = [
        'name',
        'quantity',
        'unit'
    ];

    /**
     * @throws Exception
     */
    public function list(PaginateRequest $request)
    {
        try{

            $requests = $request->all();
            $method      = $request->get('paginate', 0) == 1 ? 'paginate' : 'get';
            $methodValue = $request->get('paginate', 0) == 1 ? $request->get('per_page', 10) : '*';
            $orderColumn = $request->get('order_column') ?? 'id';
            $orderType   = $request->get('order_type') ?? 'desc';
            return Ingredient::where(function ($query) use ($requests) {
                foreach($requests as $key => $request){
                    if(in_array($key, $this->ingredientFilter)){
                        if ($key == "except") {
                            $explodes = explode('|', $request);
                            if (count($explodes)) {
                                foreach ($explodes as $explode) {
                                    $query->where('id', '!=', $explode);
                                }
                            }
                        } else {
                            if ($key == "quantity") {
                                $query->where('quantity', '>=', $request);
                            }
                            else if ($key == "unit") {
                                $query->where('unit', '=', $request);
                            }
                            else{
                                $query->where($key, 'like', '%' . $request . '%');
                            }
                        }
                    }
                }
            })->orderBy($orderColumn, $orderType)->$method(
                $methodValue
            );
        }catch (Exception $exception){
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function store(IngredientRequest $request)
    {
        try {
            DB::transaction(function () use ($request) {
                $this->ingredient = Ingredient::create($request->validated());
                $threshold_percent = Settings::group('ingredients')->get('alert_threshold_percent') ?? 10;
                $quantity = $this->ingredient->quantity ?? 0;
                $threshold = $quantity * ($threshold_percent / 100);
                $this->ingredient->max_quantity = $threshold;
                $this->ingredient->save();

                IngredientLog::create([
                    'ingredient_id' => $this->ingredient->id ?? null,
                    'before_quantity' => 0,
                    'quantity_change' => $quantity,
                    'current_quantity' => $quantity,
                    'type' => 'add'
                ]);
            });
            return $this->ingredient;
        }catch (Exception $exception){
            Log::info($exception->getMessage());
            DB::rollBack();
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function update(IngredientRequest $request, Ingredient $ingredient)
    {
        try {
            DB::transaction(function () use ($request, $ingredient) {
                $before_quantity = $ingredient->quantity ?? 0;
                $newQuantity = $request->quantity ?? 0;
                $type = $newQuantity >= $before_quantity ? 'add' : 'subtract';
                $quantity_change = abs($newQuantity - $before_quantity);

                $ingredient->update($request->validated());
                $threshold_percent = Settings::group('ingredients')->get('alert_threshold_percent') ?? 10;
                $quantity = $ingredient->quantity ?? 0;
                $threshold = $quantity * ($threshold_percent / 100);
                $ingredient->max_quantity = $threshold;
                $ingredient->save();
                IngredientLog::create([
                    'ingredient_id' => $ingredient->id ?? null,
                    'before_quantity' => $before_quantity,
                    'quantity_change' => $quantity_change,
                    'current_quantity' => $newQuantity,
                    'type' => $type,
                ]);
            });
            return Ingredient::find($ingredient->id);
        }
        catch (Exception $exception) {
            Log::info($exception->getMessage());
            DB::rollBack();
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function destroy(Ingredient $ingredient)
    {
        try{
            DB::transaction(function () use ($ingredient) {
                $ingredient->delete();
            });
        }catch (Exception $exception){
            Log::info($exception->getMessage());
            DB::rollBack();
            throw new Exception($exception->getMessage(), 422);
        }
    }
}
