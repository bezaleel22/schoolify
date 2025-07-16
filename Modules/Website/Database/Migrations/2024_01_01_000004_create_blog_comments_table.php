<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBlogCommentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('blog_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained('blog_posts')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->foreignId('parent_id')->nullable()->constrained('blog_comments')->onDelete('cascade');
            $table->string('author_name')->nullable();
            $table->string('author_email')->nullable();
            $table->string('author_avatar')->nullable();
            $table->text('content');
            $table->string('user_agent')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->enum('status', ['pending', 'approved', 'spam', 'rejected'])->default('pending');
            $table->enum('auth_provider', ['local', 'google', 'guest'])->default('guest');
            $table->string('google_id')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            
            $table->index(['post_id', 'status']);
            $table->index(['user_id']);
            $table->index(['parent_id']);
            $table->index(['status']);
            $table->index(['google_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('blog_comments');
    }
}