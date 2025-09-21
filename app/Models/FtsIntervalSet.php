<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FtsIntervalSet extends Model
{
    protected $table = 'fts_interval_sets';

    protected $fillable = [
        'universe_id','produk','n_period','k_interval','l_interval','method','u_min','u_max',
    ];

    public function universe()
    {
        return $this->belongsTo(FtsUniverse::class, 'universe_id');
    }

    public function intervals()
    {
        return $this->hasMany(FtsInterval::class, 'interval_set_id')->orderBy('urut','asc');
    }
}
