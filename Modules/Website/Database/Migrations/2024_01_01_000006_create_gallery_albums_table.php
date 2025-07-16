<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGalleryAlbumsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gallery_albums', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('cover_image')->nullable();
            $table->enum('album_type', ['photos', 'videos', 'mixed'])->default('photos');
            $table->enum('category', ['academic', 'sports', 'cultural', 'events', 'facilities', 'achievements', 'other'])->default('academic');
            $table->date('event_date')->nullable();
            $table->string('photographer')->nullable();
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->text('meta_keywords')->nullable();
            $table->json('schema_markup')->nullable();
            $table->enum('status', ['draft', 'published', 'private'])->default('published');
            $table->boolean('featured')->default(false);
            $table->integer('sort_order')->default(0);
            $table->integer('view_count')->default(0);
            $table->integer('image_count')->default(0);
            $table->timestamps();
            
            $table->index(['slug']);
            $table->index(['album_type']);
            $table->index(['category']);
            $table->index(['status']);
            $table->index(['featured']);
            $table->index(['event_date']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('gallery_albums');
    }
}