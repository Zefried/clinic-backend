<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GoogleAuthUser extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'name',
        'email',
        'user_id',
        'avatar',
        'userOAuthId',
        'profile_id_code',
        'refreshToken',
    ];

    protected $casts = [
        'profile_id_code' => 'string', // can be nullable 
    ];
    
    public function User(){
        return $this->belongsTo(User::class);
    }

}

