<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class MembershipRank extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'membership_ranks';

    protected $fillable = [
        'name',
        'min_spending',
        'priority'
    ];
    protected $dates = ['deleted_at'];

    public function user()
    {
        return $this->hasMany(User::class);
    }
}
