<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FtsMarkovCell extends Model
{
    protected $table = 'fts_markov_cells';
    protected $fillable = ['matrix_id','row_state','col_state','freq','row_total','prob'];

    public function matrix()
    {
        return $this->belongsTo(FtsMarkovMatrix::class, 'matrix_id');
    }
}
