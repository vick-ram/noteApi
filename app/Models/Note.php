<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Note extends Model
{

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    protected $fillable = ['title', 'content', 'user_id', 'favorite'];
    protected $casts = ['created_at' => 'datetime'];
}
