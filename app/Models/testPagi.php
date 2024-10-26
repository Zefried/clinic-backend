<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TestPagi extends Model
{
    use HasFactory;

    // Specify the table if it's not the plural form of the model name
    protected $table = 'test_pagis';

    // Define the fillable fields
    protected $fillable = [
        'name',
        'email',
        'phone',
        'location',
        'sex',
    ];
}
