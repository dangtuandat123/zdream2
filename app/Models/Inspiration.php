<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inspiration extends Model
{
    protected $fillable = [
        'image_url',
        'prompt',
        'ref_images',
        'is_active',
    ];

    protected $casts = [
        'ref_images' => 'array',
        'is_active' => 'boolean',
    ];
}
