<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGalleryImagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gallery_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('album_id')->constrained('gallery_albums')->onDelete('cascade');
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->string('file_name');
            $table->string('file_path');
            $table->string('file_type', 10); // jpg, png, gif, mp4, etc.
            $table->bigInteger('file_size'); // in bytes
            $table->string('mime_type');
            $table->integer('width')->nullable();
            $table->integer('height')->nullable();
            $table->integer('duration')->nullable(); // for videos in seconds
            $table->string('thumbnail_path')->nullable();
            $table->string('alt_text')->nullable();
            $table->string('caption')->nullable();
            $table->string('photographer')->nullable();
            $table->date('taken_at')->nullable();
            $table->json('exif_data')->nullable();
            $table->enum('status', ['active', 'inactive', 'processing'])->default('active');
            $table->integer('sort_order')->default(0);
            $table->integer('view_count')->default(0);
            $table->integer('download_count')->default(0);
            $table->timestamps();
            
            $table->index(['album_id']);
            $table->index(['file_type']);
            $table->index(['status']);
            $table->index(['sort_order']);
            $table->index(['taken_at']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('gallery_images');
    }
}