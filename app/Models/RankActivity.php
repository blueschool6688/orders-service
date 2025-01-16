<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RankActivity extends Model
{
    use HasFactory;

    protected $table = 'rank_activities';

    protected $fillable = [
        'user_id',
        'from_rank_id',
        'to_rank_id',
        'reason',
    ];

    protected $appends = [
      'from_rank',
      'to_rank',
    ];
    public function getFromRankAttribute(){
        return $this->belongsTo(MembershipRank::class, 'from_rank_id')->withTrashed();
    }
    public function getToRankAttribute(){
        return $this->belongsTo(MembershipRank::class, 'to_rank_id')->withTrashed();
    }
}
