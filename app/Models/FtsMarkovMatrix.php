<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FtsMarkovMatrix extends Model
{
    protected $table = 'fts_markov_matrices';
    protected $fillable = ['interval_set_id', 'k_state'];

    public function cells()
    {
        return $this->hasMany(FtsMarkovCell::class, 'matrix_id');
    }
}
