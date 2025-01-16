<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PointExchangeRate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PointExchangeController extends Controller
{
    public function index() : JsonResponse
    {
        $exchangeRate = PointExchangeRate::latest()->first();
        return new JsonResponse([
           'unit'=>$exchangeRate->unit ?? 1,
           'rate'=>$exchangeRate->rate ?? 1,
        ]);
    }
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'unit'=>'required|numeric|min:1',
            'rate'=>'required|numeric|min:1',
        ]);
        if ($validator->fails()) {
            return new JsonResponse([
                'errors' => $validator->errors(),
            ], 422);
        }
        $exchangeRate = PointExchangeRate::latest()->first();
        if ($exchangeRate) {
            $exchangeRate->update([
                'unit' => $request->input('unit')??1,
                'rate' => $request->input('rate')??1,
            ]);
        } else {
            $exchangeRate = PointExchangeRate::create([
                'unit' => $request->input('unit')??1,
                'rate' => $request->input('rate')??1,
            ]);
        }
        return new JsonResponse([
            'message' => 'Exchange rate updated successfully.',
            'unit' => $exchangeRate->unit,
            'rate' => $exchangeRate->rate,
        ],200);
    }
}
