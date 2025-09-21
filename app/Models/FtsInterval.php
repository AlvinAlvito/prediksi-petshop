<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FtsInterval extends Model
{
    protected $table = 'fts_intervals';

    protected $fillable = [
        'interval_set_id','kode','urut','lower_bound','upper_bound','mid_point',
    ];

    public function set()
    {
        return $this->belongsTo(FtsIntervalSet::class, 'interval_set_id');
    }
}
