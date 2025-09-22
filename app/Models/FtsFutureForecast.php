<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FtsFutureForecast extends Model
{
    protected $table = 'fts_future_forecasts';
    protected $fillable = [
        'interval_set_id','matrix_id','produk','start_state',
        'seq','periode_label','y_input',
        'p1','p2','p3','p4','p5',
        'f_value','f_round',
    ];
}
