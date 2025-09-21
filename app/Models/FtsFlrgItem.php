<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FtsFlrgItem extends Model
{
    protected $table = 'fts_flrg_items';
    protected $fillable = [
        'interval_set_id','current_state','next_state','freq',
    ];
}
