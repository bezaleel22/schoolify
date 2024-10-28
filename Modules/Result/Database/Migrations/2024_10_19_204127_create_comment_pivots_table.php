<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCommentPivotsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('comment_pivots', function (Blueprint $table) {
            $table->unsignedBigInteger('comment_id')->nullable();
            $table->unsignedBigInteger('comment_tag_id')->nullable(); // Changed from tag_id to comment_tag_id

            $table->foreign('comment_id')->references('id')->on('comments')->onDelete('set null');
            $table->foreign('comment_tag_id')->references('id')->on('comment_tags')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('comment_pivots');
    }
}
