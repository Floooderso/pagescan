<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactAttempt extends Model
{
    protected $fillable = ['lead_id', 'kanal', 'notiz', 'kontaktiert_am'];

    protected $casts = [
        'kontaktiert_am' => 'date',
    ];

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }
}
