<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\PointExchangeRate;
use App\Models\TopUpTransaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
class TopUpController extends Controller
{
    public function exchange(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'point' => 'required|numeric|min:0',
        ]);
        if ($validator->fails()) {
            if ($validator->fails()) {
                return new JsonResponse([
                    'errors' => $validator->errors(),
                ], 422);
            }
        }
        $point = $request->point ?? 0;
        $exchange = PointExchangeRate::exchange($point);
        return new JsonResponse([
            'point' => $point,
            'exchange' => $exchange ?? 0,
        ], 200);
    }

    public function createRequest(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'points' => 'required|numeric|min:1',
            'bonus_points' => 'required|numeric|min:0',
            'totalPrice' => 'required|numeric|min:1',
            'payment_method' => 'required|string',
            'package_id' => 'nullable',
        ]);

        if ($validator->fails()) {
            return new JsonResponse([
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = auth('sanctum-client')->user();

        if (!$user) {
            return new JsonResponse([
                'authenticated' => false,
                'message' => 'User is not authenticated.',
            ], 401);
        }
        $data =
            [
                'user_id' => $user->id??null,
                'amount' => $request->input('totalPrice') ?? 0,
                'points' => $request->input('points') ?? 0,
                'bonus_points' => $request->input('bonus_points') ?? 0,
                'package_id' => $request->input('package_id') ?? null,
                'payment_method' => $request->input('payment_method')?? null,
                'status' => 'pending',
                'transaction_id' => uniqid(),
            ];
        try {
            DB::beginTransaction();
            $topUpTransaction = TopUpTransaction::create($data);
            DB::commit();
            return new JsonResponse([
                'success' => true,
                'message' => 'Top-up request created successfully.',
                'data' => $topUpTransaction,
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return new JsonResponse([
                'success' => false,
                'message' => 'Failed to create top-up request.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
