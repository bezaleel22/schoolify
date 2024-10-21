<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStudentRatingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('student_ratings', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('rate')->nullable();
            $table->string('attribute', 200)->nullable();
            $table->string('color', 200)->nullable();
            $table->string('remark', 200)->nullable();
            $table->timestamps();

            $table->integer('student_id')->nullable()->unsigned();
            $table->foreign('student_id')->references('id')->on('sm_students')->onDelete('set null');

            $table->integer('exam_type_id')->nullable()->unsigned();
            $table->foreign('exam_type_id')->references('id')->on('sm_exam_types')->onDelete('set null');

            $table->integer('academic_id')->nullable()->unsigned();
            $table->foreign('academic_id')->references('id')->on('sm_academic_years')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('student_ratings');
    }
}
