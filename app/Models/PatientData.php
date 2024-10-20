<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientData extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'age',
        'sex',
        'relativeName',
        'phone',
        'email',
        'identityProof',
        'village',
        'po',
        'ps',
        'pin',
        'district',
        'state',
        'unique_patient_id',
        'request_status', 
        'associated_user_email', 
        'associated_user_id',
        'disable',
    ];
    
}
