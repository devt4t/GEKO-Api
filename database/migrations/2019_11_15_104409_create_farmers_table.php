<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFarmersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('farmers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('farmer_no')->unique();
            $table->string('name');
            $table->date('birthday');
            $table->enum('religion', ['-','islam', 'kristen', 'khatolik', 'hindu', 'buddha', 'konghuchu', 'others']);
            $table->string('ethnic');
            $table->enum('origin', ['-','lokal', 'pendatang']);
            $table->enum('gender',['male','female']);
            $table->date('join_date')->nullable();
            $table->integer('number_family_member');
            $table->string('ktp_no')->nullable();
            $table->string('phone')->nullable();;
            $table->string('address')->nullable();;
            $table->string('village')->nullable();;
            $table->string('kecamatan')->nullable();;
            $table->string('city')->nullable();;
            $table->string('province')->nullable();;
            $table->string('post_code')->nullable();
            $table->string('mu_no');
            $table->string('target_area');
            $table->string('group_no')->nullable();
            $table->string('mou_no')->nullable();
            $table->string('main_income')->nullable();
            $table->string('side_income')->nullable();
            $table->boolean('active');
            $table->integer('user_id');
            $table->timestamps();
            $table->string('ktp_document')->nullable();
            $table->string('farmer_profile')->nullable();
            $table->enum('marrital_status',['-','Kawin', 'Belum Kawin', 'Janda', 'Duda']);
            $table->string('main_job')->nullable();
            $table->string('side_job')->nullable();
            $table->enum('education',['-','Tidak Sekolah', 'SD', 'SMP', 'SMA', 'Diploma', 'Sarjana', 'Magister', 'Doktor'])->nullable();
            $table->enum('non_formal_education', ['-','Tidak Ada', 'Pesantren', 'Kursus/Pelatihan'])->nullable();
            $table->boolean('is_dell')->default(0);
            $table->boolean('complete_data')->default(0);
            $table->boolean('approve')->default(0);
        });

        Schema::create('farmer_testimonial', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('farmer_no');
            $table->foreign('farmer_no')->references('farmer_no')->on('farmers');
            $table->string('testimonial');
            $table->date('year');
            $table->timestamps();
        });

        Schema::create('farmer_stories', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('farmer_no');
            $table->foreign('farmer_no')->references('farmer_no')->on('farmers');
            $table->string('story_no');
            $table->string('story_title');
            $table->date('year');
            $table->string('story_description');
            $table->boolean('active');
            $table->timestamps();
        });

        Schema::create('farmer_photos', function (Blueprint $table){
            $table->bigIncrements('id');
            $table->string('farmer_no');
            $table->foreign('farmer_no')->references('farmer_no')->on('farmers');
            $table->string('filename');
            $table->string('path');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('farmer_testimonial');
        Schema::dropIfExists('farmer_stories');
        Schema::dropIfExists('farmer_photos');
        Schema::dropIfExists('farmers');
    }
}
