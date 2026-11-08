<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserNotificationToken extends Model
{
    protected $fillable = [
        'token', 'user_id', 'status'
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}