<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['score', 'page_count', 'report'])]
class SeoAudit extends Model
{
    protected function casts(): array
    {
        return ['score' => 'integer', 'page_count' => 'integer', 'report' => 'array'];
    }
}
