<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStaffMembersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('staff_members', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('slug')->unique();
            $table->string('title')->nullable(); // Dr., Prof., Mr., Mrs., etc.
            $table->string('position');
            $table->string('department')->nullable();
            $table->longText('bio')->nullable();
            $table->text('short_bio')->nullable();
            $table->string('profile_image')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('office_location')->nullable();
            $table->json('qualifications')->nullable(); // Array of degrees/certifications
            $table->json('specializations')->nullable(); // Areas of expertise
            $table->json('research_interests')->nullable();
            $table->json('publications')->nullable();
            $table->json('awards')->nullable();
            $table->json('social_links')->nullable(); // LinkedIn, Twitter, etc.
            $table->string('office_hours')->nullable();
            $table->date('join_date')->nullable();
            $table->integer('years_experience')->nullable();
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->text('meta_keywords')->nullable();
            $table->json('schema_markup')->nullable();
            $table->enum('status', ['active', 'inactive', 'retired'])->default('active');
            $table->boolean('featured')->default(false);
            $table->boolean('show_on_website')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            
            $table->index(['slug']);
            $table->index(['department']);
            $table->index(['status']);
            $table->index(['featured']);
            $table->index(['show_on_website']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('staff_members');
    }
}