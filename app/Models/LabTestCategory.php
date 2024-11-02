<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LabTestCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'test_category_id',
        'lab_id',
        'lab_name',
        'disable_status',
    ];

    // Relationship: LabTestCategory belongs to a LabModel
    public function lab()
    {
        return $this->belongsTo(LabModel::class, 'lab_id');
    }

    // Relationship: LabTestCategory has many LabTests
    public function labTests()
    {
        return $this->hasMany(LabTest::class, 'lab_test_category_id');
    }
}
