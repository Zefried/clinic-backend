<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Patient_location_Count extends Model
{
    use HasFactory;
        
    protected $fillable = [
        'location_id',
        'patient_card_id',
        'patient_id',
        'associated_user_id',
    ];
}
