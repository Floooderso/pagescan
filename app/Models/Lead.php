<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    protected $fillable = ['user_id', 'company_id', 'scan_id', 'status', 'priority', 'notes', 'assigned_to'];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function scan()
    {
        return $this->belongsTo(Scan::class);
    }

    public function contactAttempts()
    {
        return $this->hasMany(ContactAttempt::class)->orderBy('kontaktiert_am', 'desc');
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
