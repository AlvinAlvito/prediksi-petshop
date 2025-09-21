<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FtsFuzzySet extends Model
{
    protected $table = 'fts_fuzzy_sets';
    protected $fillable = [
        'interval_set_id','kode','urut',
        'mu_u1','mu_u2','mu_u3','mu_u4','mu_u5',
    ];
}
