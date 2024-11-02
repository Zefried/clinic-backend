<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LabTest extends Model
{
    use HasFactory;

    protected $fillable = [
        'lab_test_name',
        'lab_id',
        'lab_test_category_id',
        'lab_name',
        'disable_status',
        'lab_test_id',
    ];

    // Relationship: LabTest belongs to a LabModel
    public function lab()
    {
        return $this->belongsTo(LabModel::class, 'lab_id');
    }

    // Relationship: LabTest belongs to a LabTestCategory
    public function labTestCategory()
    {
        return $this->belongsTo(LabTestCategory::class, 'lab_test_category_id');
    }
}
