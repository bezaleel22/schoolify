<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEventsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->longText('description')->nullable();
            $table->text('excerpt')->nullable();
            $table->string('featured_image')->nullable();
            $table->json('gallery')->nullable();
            $table->string('location')->nullable();
            $table->text('location_address')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->dateTime('start_date');
            $table->dateTime('end_date')->nullable();
            $table->boolean('all_day')->default(false);
            $table->string('timezone')->default('UTC');
            $table->enum('event_type', ['academic', 'sports', 'cultural', 'workshop', 'meeting', 'holiday', 'exam', 'other'])->default('academic');
            $table->enum('audience', ['all', 'students', 'parents', 'staff', 'public'])->default('all');
            $table->string('organizer_name')->nullable();
            $table->string('organizer_email')->nullable();
            $table->string('organizer_phone')->nullable();
            $table->boolean('registration_required')->default(false);
            $table->string('registration_link')->nullable();
            $table->decimal('price', 8, 2)->nullable();
            $table->integer('max_attendees')->nullable();
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->text('meta_keywords')->nullable();
            $table->json('schema_markup')->nullable();
            $table->enum('status', ['draft', 'published', 'cancelled', 'postponed'])->default('published');
            $table->boolean('featured')->default(false);
            $table->integer('view_count')->default(0);
            $table->timestamps();
            
            $table->index(['slug']);
            $table->index(['start_date', 'end_date']);
            $table->index(['event_type']);
            $table->index(['audience']);
            $table->index(['status']);
            $table->index(['featured']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('events');
    }
}