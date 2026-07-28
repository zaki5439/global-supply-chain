<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Watchlist extends Model
{
    protected $fillable = [
        'user_id',
        'watchable_type',
        'watchable_id',
        'notify_on_high_risk'
    ];

    public function watchable()
    {
        return $this->morphTo();
    }
}
