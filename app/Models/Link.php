<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Link extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'original_url',
        'short_token',
        'is_private',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
