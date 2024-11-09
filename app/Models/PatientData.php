<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientData extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'patient_location',
        'patient_location_id',
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
        'disable_status',
    ];
    

    public function patientLocationCount()
    {
        return $this->hasOne(Patient_location_Count::class, 'patient_id');
    }
}
