<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_submissions', function (Blueprint $table) {
            $table->id();
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('email', 254);
            $table->string('country_code', 4);
            $table->string('phone', 25);
            $table->string('company', 150);
            $table->string('job_title', 120)->nullable();
            $table->string('budget', 120)->nullable();
            $table->string('referral_source', 30);
            $table->text('message');
            $table->string('locale', 2);
            $table->string('market', 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_submissions');
    }
};
