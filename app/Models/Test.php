<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Test extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',       
        'description',  
        'test_category_id', 
        'status',
        'disable_status',  
    ];

    public function testCategory()
    {
        return $this->belongsTo(TestCategory::class);
    }
}
