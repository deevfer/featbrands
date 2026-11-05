<?php

namespace App\Models;

use Database\Factories\SeoPageFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['route_name', 'locale', 'title', 'description', 'image'])]
class SeoPage extends Model
{
    /** @use HasFactory<SeoPageFactory> */
    use HasFactory;
}
