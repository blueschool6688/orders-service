<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class SpecialPointPackage extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'special_point_packages';

    protected $dates = ['deleted_at'];

    protected $fillable = [
        'name',
        'price',
        'points',
        'rank_id',
        'thumbnail',
        'description',
        'status',
    ];
    public function rank(){
        return $this->belongsTo(MembershipRank::class, 'rank_id')->withTrashed();
    }
    public function topUpTransactions(){
        return $this->hasMany(TopUpTransaction::class, 'package_id');
    }

    public function isActive():bool{
        return $this->status === 1;
    }

}
