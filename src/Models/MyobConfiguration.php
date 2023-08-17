<?php

namespace Webfox\MYOB\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MyobConfiguration extends Model
{
    use HasFactory;

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    protected $appends = [
        'has_configured_company_file',
    ];

    protected $hidden = [
        'access_token',
        'refresh_token',
        'company_file_token',
    ];

    public static function booted()
    {
        static::saving(function (MyobConfiguration $model) {
            $model->expires_at = now()->addSeconds(900);
        });
    }

    public function hasConfiguredCompanyFile(): Attribute
    {
        return Attribute::make(
            get: function ($val, $attributes) {
                return $attributes['company_file_id']
                    && $attributes['company_file_token']
                    && $attributes['company_file_name']
                    && $attributes['company_file_uri'];
            }
        );
    }
}
