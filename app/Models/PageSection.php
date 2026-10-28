<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PageSection extends Model
{
    protected $fillable = [
        'page',
        'section',
        'content_es',
        'content_en',
    ];

    protected function casts(): array
    {
        return [
            'content_es' => 'array',
            'content_en' => 'array',
        ];
    }
}
