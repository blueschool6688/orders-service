<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PointTransaction extends Model
{
    const VALID_TYPES = [
        'purchase', 'refund', 'bonus', 'payment'
    ];
    use HasFactory;
    protected $table = 'point_transactions';

    protected $fillable = [
        'user_id',
        'previous_balance',
        'point_change',
        'current_balance',
        'type',
        'refund_from_transaction',
        'comment'
    ];

    protected $casts = [
      'type'=>'string',
    ];
    public function user(){
        return $this->belongsTo(User::class);
    }
    public function refundSource()
    {
        return $this->belongsTo(self::class, 'refund_from_transaction');
    }

    public function refunds()
    {
        return $this->hasMany(self::class, 'refund_from_transaction');
    }
    public static function addLog(array $data): self
    {
        return self::create($data);
    }
    public function switchType(string $newType): self
    {

        if (!in_array($newType, self::VALID_TYPES)) {
            throw new \InvalidArgumentException("Invalid type: {$newType}");
        }

        $this->update(['type' => $newType]);

        return $this;
    }
    public function setComment(string $comment): self
    {
        $this->update(['comment' => $comment]);

        return $this;
    }
    public function refund(string $comment = 'Refund initiated'): self
    {
        if ($this->type !== 'payment') {
            throw new \InvalidArgumentException('Refund can only be applied to payment transactions.');
        }

        if ($this->refunds()->exists()) {
            throw new \InvalidArgumentException('This transaction has already been refunded.');
        }
        $refundAmount = -($this->point_change ?? 0);
        $currentBalance = $this->current_balance ?? 0;
        $newBalance = max(0, $currentBalance + $refundAmount);

        $refundTransaction = self::addLog([
            'user_id' => $this->user_id,
            'previous_balance' => $currentBalance,
            'point_change' => $newBalance - $currentBalance,
            'current_balance' => $newBalance,
            'type' => 'refund',
            'refund_from_transaction' => $this->id,
            'comment' => $comment,
        ]);
        $this->user->updateBalance($newBalance - $currentBalance);
        return $refundTransaction;
    }
}
