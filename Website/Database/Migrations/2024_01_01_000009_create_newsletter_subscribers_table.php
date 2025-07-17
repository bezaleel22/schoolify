<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNewsletterSubscribersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('newsletter_subscribers', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->enum('status', ['subscribed', 'unsubscribed', 'pending', 'bounced'])->default('pending');
            $table->json('interests')->nullable(); // Store subscriber interests/categories
            $table->json('preferences')->nullable(); // Email frequency, format preferences
            $table->string('subscription_source')->nullable(); // Where they subscribed from
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->string('referrer')->nullable();
            $table->string('verification_token')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('subscribed_at')->nullable();
            $table->timestamp('unsubscribed_at')->nullable();
            $table->string('unsubscribe_reason')->nullable();
            $table->integer('email_opens')->default(0);
            $table->integer('email_clicks')->default(0);
            $table->timestamp('last_email_sent_at')->nullable();
            $table->timestamp('last_activity_at')->nullable();
            $table->boolean('double_opt_in')->default(true);
            $table->timestamps();
            
            $table->index(['email']);
            $table->index(['status']);
            $table->index(['verified_at']);
            $table->index(['subscribed_at']);
            $table->index(['last_activity_at']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('newsletter_subscribers');
    }
}