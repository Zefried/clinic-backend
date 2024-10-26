<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LabModel extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'user_id',
        'phone',
        'email',
        'registrationNo',
        'buildingNo',
        'landmark',
        'district',
        'state',
        'lab_account_request',
        'lab_unique_id',
        'disable_status',
    ];

    // Relationship: LabModel belongs to a user
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
