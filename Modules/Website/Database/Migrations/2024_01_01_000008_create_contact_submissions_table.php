<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateContactSubmissionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contact_submissions', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('subject');
            $table->longText('message');
            $table->enum('inquiry_type', ['general', 'admission', 'employment', 'complaint', 'suggestion', 'other'])->default('general');
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->string('referrer')->nullable();
            $table->json('form_data')->nullable(); // Store additional form fields
            $table->enum('status', ['new', 'read', 'replied', 'resolved', 'spam'])->default('new');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null');
            $table->text('internal_notes')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamp('replied_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->boolean('is_spam')->default(false);
            $table->float('spam_score')->nullable();
            $table->timestamps();
            
            $table->index(['email']);
            $table->index(['status']);
            $table->index(['inquiry_type']);
            $table->index(['priority']);
            $table->index(['assigned_to']);
            $table->index(['created_at']);
            $table->index(['is_spam']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('contact_submissions');
    }
}