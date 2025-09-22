<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FtsMapeSummary extends Model
{
    protected $table = 'fts_mape_summaries';
    protected $fillable = ['interval_set_id','n_rows','sum_ape','mape_pct'];
}
