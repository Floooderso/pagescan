<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SavedSearch extends Model
{
    protected $fillable = [
        'user_id', 'name', 'branche', 'stadt',
        'last_run_at', 'last_result_count', 'new_leads_count',
    ];

    protected $casts = [
        'last_run_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
