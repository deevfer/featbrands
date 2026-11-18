<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['first_name', 'last_name', 'email', 'country_code', 'phone', 'company', 'job_title', 'budget', 'referral_source', 'message', 'locale', 'market'])]
class ContactSubmission extends Model
{
    public function fullName(): string
    {
        return trim($this->first_name.' '.$this->last_name);
    }

    public function fullPhone(): string
    {
        return trim($this->country_code.' '.$this->phone);
    }
}
