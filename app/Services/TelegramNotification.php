<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use TelegramBot\Api\BotApi;
class  TelegramNotification
{
    protected $telegram;
    protected $chatId;

    /**
     * @throws Exception
     */
    public function __construct()
    {
        $this->telegram = new BotApi(env('TELEGRAM_BOT_TOKEN',''));
        $this->chatId = env('TELEGRAM_CHAT_ID','');
    }

    public function sendMessage(string $message){
        try {
            $this->telegram->sendMessage($this->chatId,$message,'Markdown');
        }catch (Exception $e){
            Log::error($e->getMessage());
        }
    }

    public function notifyLowStock(string $ingredientName, int $currentQuantity, int $maxQuantity, string $unit = ''): void
    {
        $message = sprintf(
            "📢 *CẢNH BÁO NGUYÊN LIỆU SẮP HẾT!*\n\n" .
            "🔖 Tên nguyên liệu: *%s*\n" .
            "📦 Số lượng tồn kho hiện tại: *%d %s*\n" .
            "📊 Ngưỡng báo động: *%d %s*\n" .
            "🛠️ Vui lòng kiểm tra và bổ sung nguyên liệu kịp thời.\n\n" .
            "🕒 Thời gian: *%s*",
            $ingredientName,
            $currentQuantity,
            $unit,
            $maxQuantity,
            $unit,
            now()->format('Y-m-d H:i:s')
        );
        $this->sendMessage($message);
    }
}
