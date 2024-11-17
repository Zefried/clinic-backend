<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientAssignedData extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'patient_status',
        'patient_name',
        'lab_id',
        'lab_name',
        'employee_id',
        'employee_name',
        'discount',
        'final_discount',
        'associated_sewek_id',
        'disable_status',
        'doc_path',
        'test_ids',
        'visit',
        'final_amount',
    ];



    // Relationship with Employee model
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    // Relationship with Lab model (just in case)
    public function lab()
    {
        return $this->belongsTo(LabModel::class, 'lab_id');
    }
}
