<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Contact forms that a visitor started but may not send, kept for the 30-minute follow-up and retargeting.
     */
    public function up(): void
    {
        Schema::create('contact_intents', function (Blueprint $table) {
            $table->id();
            $table->uuid('intent_key')->unique();
            $table->string('first_name', 100);
            $table->string('last_name', 100)->nullable();
            $table->string('email', 254)->index();
            $table->string('country_code', 4)->nullable();
            $table->string('phone', 25)->nullable();
            $table->string('company', 150)->nullable();
            $table->string('job_title', 120)->nullable();
            $table->string('budget', 120)->nullable();
            $table->string('referral_source', 30)->nullable();
            $table->text('message')->nullable();
            $table->string('locale', 2);
            $table->string('market', 2);
            $table->boolean('marketing_consent')->default(false);
            $table->json('tracking')->nullable();
            $table->foreignId('contact_submission_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('last_activity_at')->index();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('abandoned_notified_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_intents');
    }
};
