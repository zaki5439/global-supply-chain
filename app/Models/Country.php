<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    protected $fillable = [
        'name',
        'official_name',
        'iso2_code',
        'iso3_code',
        'region',
        'sub_region',
        'currencies',
        'languages',
        'latitude',
        'longitude',
        'flag_emoji',
        'capital',
        'population',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'population' => 'integer',
    ];

    /**
     * Get countries sorted by name (A-Z)
     */
    public static function sortedByName()
    {
        return static::orderBy('name', 'asc');
    }

    /**
     * Get countries by region
     */
    public function scopeByRegion($query, $region)
    {
        return $query->where('region', $region);
    }

    /**
     * Get countries by language
     */
    public function scopeByLanguage($query, $language)
    {
        return $query->where('languages', 'like', "%{$language}%");
    }

    /**
     * Accessor: Get coordinates as array
     */
    public function getCoordinatesAttribute()
    {
        return [
            'lat' => $this->latitude,
            'lon' => $this->longitude
        ];
    }
}
