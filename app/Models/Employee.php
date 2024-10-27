<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'role',
        'lab_id',
        'lab_name',
        'lab_location',
        'disable_status',
    ];

    public function lab()
    {
        return $this->belongsTo(LabModel::class, 'lab_id');
    }
}
