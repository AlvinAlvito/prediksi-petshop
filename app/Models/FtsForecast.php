<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FtsForecast extends Model
{
    protected $table = 'fts_forecasts';

    protected $fillable = [
        'universe_id',
        'interval_set_id',
        'matrix_id',
        'produk',
        'urut',
        'periode_label',
        'y_actual',
        'y_prev',
        'prev_state',
        'curr_state',
        'next_state',
        'p1',
        'p2',
        'p3',
        'p4',
        'p5',
        'f_value',
        'dt',
        'f_final',
        'f_final_round',   // <— baru
        'ape',
    ];


}
