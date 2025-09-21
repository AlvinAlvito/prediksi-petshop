<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FtsFuzzification extends Model
{
    protected $table = 'fts_fuzzifications';
    protected $fillable = [
        'universe_id','interval_set_id','produk',
        'urut','periode_label','nilai','interval_kode','fuzzy_kode',
    ];
}
