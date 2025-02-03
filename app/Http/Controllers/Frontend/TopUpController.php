<?php

namespace App\Http\Controllers\Frontend;

use App\Events\OrderPaymentUpdated;
use App\Events\UpdateUserPoint;
use App\Http\Controllers\Controller;
use App\Http\Resources\OrderDetailsResource;
use App\Http\Resources\TopUpPackageResource;
use App\Http\Resources\UserResource;
use App\Libraries\AppLibrary;
use App\Models\Order;
use App\Models\PointExchangeRate;
use App\Models\PointTransaction;
use App\Models\TopUpTransaction;
use App\Models\User;
use App\Services\TopUpPackageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Mockery\Exception;
use App\Http\Requests\PaginateRequest;
class TopUpController extends Controller
{
    private TopUpPackageService $topUpPackageService;
    public function __construct(TopUpPackageService $topUpPackageService){
        $this->topUpPackageService = $topUpPackageService;
    }
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
    public function show($id) : JsonResponse
    {
        if (!$id){
            return new JsonResponse([
               'status'=>false,
               'message'=>'Missing parameters',
            ],500);
        }
        $user = auth('sanctum-client')->user();
        if (!$user) {
            return new JsonResponse([
                'authenticated' => false,
                'message' => 'User is not authenticated.',
            ], 401);
        }
        $request = TopUpTransaction::where('id', $id)->where('user_id', $user->id)->first();
        if (!$request) {
            return new JsonResponse([
                'status' => false,
                'message' => 'Request not found.',
            ], 404);
        }
        $request->amount = AppLibrary::flatAmountFormat($request->amount??0);
        $request->point = AppLibrary::flatAmountFormat($request->point??0);
        $request->bonus_points = AppLibrary::flatAmountFormat($request->bonus_points??0);
        return new JsonResponse([
            'status' => true,
            'data' => $request,
        ], 200);
    }
    public function webhookSepay(Request $request) : JsonResponse | UserResource
    {
        Log::info('Webhook received');
        $webhookData = $request->all();
        $logMessage = 'Sepay ' . "\n" . json_encode($webhookData, JSON_PRETTY_PRINT) . "\n------------------------------\n";
        $filePath = storage_path('logs/sepay.txt');
        File::append($filePath, $logMessage);
        $code = $webhookData['code'] ?? null;
        $requestId = AppLibrary::getCodeFromContent($code);
        $transferAmount = $webhookData['transferAmount'] ?? 0;
        $currencyAmountFormat = AppLibrary::currencyAmountFormat($transferAmount);
        $transactionId = $webhookData['id']??null;
        try{
            DB::beginTransaction();
            $topUpTransaction = TopUpTransaction::find($requestId);
            if (!$topUpTransaction){
                $logMessage = "Order Not found with Id ". $requestId;
                File::append($filePath, $logMessage);
                return new JsonResponse([
                    'status' => false,
                    'message' => $logMessage,
                ]);
            }
            $total = $topUpTransaction->amount ?? 0;
            if ($transferAmount == $total){
                $topUpTransaction->markAsSuccess();
                $topUpTransaction->transaction_id = $transactionId;
                $topUpTransaction->save();
                $userId = $topUpTransaction->user_id??null;
                if (!$userId){
                    return new JsonResponse([
                        'status' => false,
                        'message' => "User is missing.",
                    ]);
                }
                $user = User::find($userId);
                if (!$user){
                    return new JsonResponse([
                        'status' => false,
                        'message' => "User not found.",
                    ]);
                }
                $currentBalance = $user->balance??0;
                $pointChange = ($topUpTransaction->points ?? 0) + ($topUpTransaction->bonus_points ?? 0);
                $newBalance = $currentBalance + $pointChange;
                $logPointTransaction = [
                    'user_id' => $userId??null,
                    'previous_balance' => $currentBalance,
                    'point_change' => $pointChange,
                    'current_balance' => $newBalance,
                    'type' => 'purchase',
                    'comment' => "Nạp thành công " . $pointChange . " điểm với số tiền " . $currencyAmountFormat,
                ];
                $user->balance = $newBalance;
                $user->save();
                PointTransaction::addLog($logPointTransaction);
                event(new UpdateUserPoint($user));
                DB::commit();
                return new JsonResponse([
                    'status' => true,
                    'message' => "Top-up request updated successfully.",
                    'data' => $topUpTransaction,
                ]);
            }
            else{
                File::append($filePath, "Transfer amount does not match total. Request ID: {$requestId}\n");
                return new JsonResponse([
                    'status' => false,
                    'message' => "Transfer amount does not match the total amount.",
                ]);
            }
        }catch (Exception $exception){
            DB::rollBack();
            return new JsonResponse([
                'status'=>false,
                'message'=>$exception->getMessage(),
            ]);
        }
    }

    public function completeOrder(Request $request) : JsonResponse  |OrderDetailsResource
    {
        Log::info('Complete order webhook');
        $webhookData = $request->all();
        $logMessage = 'Complete order webhook' .'\n' . json_encode($webhookData, JSON_PRETTY_PRINT);
        $filePath = storage_path('logs/sepay.txt');
        File::append($filePath, $logMessage);
        $code = $webhookData['code'] ?? null;
        $orderId = AppLibrary::getOrderIdFromContent($code);
        $transferAmount = $webhookData['transferAmount'] ?? 0;
        $transactionId = $webhookData['id']??null;
        try {
            DB::beginTransaction();
            $order = Order::find($orderId);
            if (!$order){
                $logMessage = "Order Not found with Id ". $orderId;
                File::append($filePath, $logMessage);
                return new JsonResponse([
                    'status' => false,
                    'message' => $logMessage,
                ]);
            }
            $total = $order->total ?? 0;
            if ($transferAmount == $total){
                $order->payment_status = 5;
                $order->save();
                event(new OrderPaymentUpdated($order));
                DB::commit();
                return new JsonResponse([
                    'status' => true,
                    'message' => "Order payment updated successfully.",
                ]);
            }
            else{
                File::append($filePath, "Transfer amount does not match total. Order ID: {$orderId}\n");
                return new JsonResponse([
                    'status' => false,
                    'message' => "Transfer amount does not match the total amount.",
                ]);
            }
        }catch (Exception $exception){
            DB::rollBack();
            return new JsonResponse([
                'status'=>false,
                'message'=>$exception->getMessage(),
            ]);
        }
    }

    public function list(PaginateRequest $request)
    {
        try {
            return TopUpPackageResource::collection($this->topUpPackageService->list($request));
        } catch (\Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }
}
