<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class Ingredient extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $table = 'ingredients';

    protected $fillable = [
        'name',
        'quantity',
        'max_quantity',
        'unit'
    ];
    protected $dates = ['deleted_at'];

    public function items()
    {

    }

    /**
     * Kiểm tra nguyên liệu có đủ không
     * @param float $requiredQuantity
     * @return bool
     */
    public function isSufficient(float $requiredQuantity): bool
    {
        return $this->quantity >= $requiredQuantity;
    }


    /**
     * Trừ số lượng nguyên liệu
     * @param float $amount
     * @throws \Exception
     */
    public function deductQuantity(float $amount): void
    {
        if ($this->isSufficient($amount)) {
            $this->quantity = max(0, $this->quantity - $amount);
            $this->save();
        } else {
            throw new \Exception("Không đủ nguyên liệu: {$this->name}");
        }
    }
}
