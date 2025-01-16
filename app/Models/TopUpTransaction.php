<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TopUpTransaction extends Model
{
    use HasFactory;

    const VALID_STATUSES = [
        'pending',
        'success',
        'failed',
    ];
    protected $table = 'top_up_transactions';
    protected $fillable = [
        'user_id',
        'amount',
        'points',
        'bonus_points',
        'package_id',
        'payment_method',
        'status',
        'transaction_id'
    ];

    protected $appends = [
      'total_points',
    ];
    public function user(){
        return $this->belongsTo(User::class);
    }
    public function package(){
        return $this->belongsTo(SpecialPointPackage::class,'package_id');
    }
    public function getTotalPointsAttribute(){
        return ($this->points ?? 0) + ($this->bonus_points ?? 0);
    }
    public function markAsSuccess():void{
        $this->update(['status' => 'success']);
    }
    public function markAsFailed():void{
        $this->update(['status' => 'failed']);
    }
    public function markAsPending():void{
        $this->update(['status' => 'pending']);
    }
}
