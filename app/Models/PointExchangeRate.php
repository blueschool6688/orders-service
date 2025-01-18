<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class PointExchangeRate extends Model
{
    use HasFactory;
    protected $table = 'point_exchange_rates';

    protected $fillable = [
        'unit',
        'rate'
    ];
    public static function exchange(int $points){

        if ($points < 0){
            return 0;
        }
        $currentRate = self::latest()->first();
        if (!$currentRate) {
            Log::error('Exchange rate not configured');
            return 0;
        }
        $rate = $currentRate->rate??1;
        $unit = $currentRate->unit??1;

        return ($points * $rate) / $unit;
    }
    public static function exchangeToPoint(int $price){
        if($price < 0 || !is_numeric($price)){
            return 0;
        }
        $currentRate = self::latest()->first();
        if (!$currentRate) {
            Log::error('Exchange rate not configured');
            return 0;
        }
        $rate = $currentRate->rate??1;
        $unit = $currentRate->unit??1;

        return ($price * $unit) / $rate;
    }
}
