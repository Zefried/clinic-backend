<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TestCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'status',
        'disable_status',
    ];


    public function tests()
    {
        return $this->hasMany(Test::class)->where('disable_status', '!=', '1');
    }
}
