<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PointExchangeRate extends Model
{
    use HasFactory;
    protected $table = 'point_exchange_rates';

    protected $fillable = [
        'unit',
        'rate'
    ];
}
