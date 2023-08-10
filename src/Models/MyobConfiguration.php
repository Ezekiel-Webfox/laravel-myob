<?php

namespace Webfox\MYOB\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MyobConfiguration extends Model
{
    use HasFactory;

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public static function booted()
    {
        static::saving(function (MyobConfiguration $model) {
            $model->expires_at = now()->addSeconds(900);
        });
    }
}
