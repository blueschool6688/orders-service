<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IngredientLog extends Model
{
    use HasFactory;

    protected $table = 'ingredient_logs';

    protected $fillable = [
        'ingredient_id',
        'before_quantity',
        'quantity_change',
        'current_quantity',
        'type'
    ];
    public $timestamps = false;
}
