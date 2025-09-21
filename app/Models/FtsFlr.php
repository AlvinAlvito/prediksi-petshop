<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FtsFlr extends Model
{
    protected $table = 'fts_flrs';
    protected $fillable = [
        'interval_set_id','urut_from','urut_to','periode_from','periode_to','state_from','state_to',
    ];
}
