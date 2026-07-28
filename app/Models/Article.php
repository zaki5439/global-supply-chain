<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Article extends Model
{
    protected $fillable = [
        'country_id',
        'port_id',
        'title',
        'summary',
        'url',
        'source_name',
        'published_at',
        'sentiment_score',
        'sentiment_label'
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'sentiment_score' => 'float',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function port(): BelongsTo
    {
        return $this->belongsTo(Port::class);
    }
}
