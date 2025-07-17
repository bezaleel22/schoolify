<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePageAnalyticsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('page_analytics', function (Blueprint $table) {
            $table->id();
            $table->string('page_type'); // page, blog_post, event, etc.
            $table->unsignedBigInteger('page_id');
            $table->string('url');
            $table->string('title')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->string('referrer')->nullable();
            $table->string('country')->nullable();
            $table->string('city')->nullable();
            $table->string('device_type')->nullable(); // desktop, mobile, tablet
            $table->string('browser')->nullable();
            $table->string('platform')->nullable(); // Windows, iOS, Android, etc.
            $table->integer('session_duration')->nullable(); // in seconds
            $table->boolean('is_bounce')->default(false);
            $table->integer('scroll_depth')->nullable(); // percentage
            $table->json('utm_parameters')->nullable(); // UTM tracking
            $table->json('custom_events')->nullable(); // Downloads, video plays, etc.
            $table->date('visit_date');
            $table->time('visit_time');
            $table->timestamp('visited_at');
            $table->string('session_id')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            
            $table->index(['page_type', 'page_id']);
            $table->index(['url']);
            $table->index(['visit_date']);
            $table->index(['visited_at']);
            $table->index(['user_id']);
            $table->index(['session_id']);
            $table->index(['country']);
            $table->index(['device_type']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('page_analytics');
    }
}