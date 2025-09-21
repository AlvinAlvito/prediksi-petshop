<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FtsUniverse extends Model
{
    protected $table = 'fts_universes';

    protected $fillable = [
        'produk', 'periode_mulai', 'periode_selesai',
        'n', 'dmin', 'dmax', 'd1', 'd2', 'u_min', 'u_max',
        'input_series',
    ];

    protected $casts = [
        'periode_mulai' => 'date',
        'periode_selesai' => 'date',
        'input_series' => 'array',
    ];
}
