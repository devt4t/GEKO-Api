<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLahansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lahans', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('lahan_no')->unique();
            $table->string('document_no');
            $table->float('land_area');
            $table->integer('planting_area')->nullable();
            $table->string('longitude');
            $table->string('latitude');
            $table->string('coordinate');
            $table->string('polygon')->nullable();
            $table->string('village')->nullable();
            $table->string('kecamatan')->nullable();
            $table->string('city')->nullable();
            $table->string('province')->nullable();
            $table->string('description')->nullable();
            $table->string('elevation')->nullable();
            $table->string('soil_type')->nullable();
            $table->string('current_crops')->nullable();
            $table->boolean('active');
            $table->string('farmer_no')->nullable();
            $table->foreign('farmer_no')->references('farmer_no')->on('farmers')->onUpdate('cascade')->onDelete('set null')->onAdd('set null');
            $table->string('farmer_temp')->nullable();
            $table->string('mu_no');
            $table->string('target_area');
            $table->integer('user_id');
            $table->timestamps();
            $table->string('sppt')->nullable();
            $table->string('tutupan_lahan')->nullable();
            $table->string('photo1')->nullable();
            $table->string('photo2')->nullable();
            $table->string('photo3')->nullable();
            $table->string('photo4')->nullable();
            $table->string('group_no')->nullable();
            $table->string('kelerengan_lahan')->nullable();
            $table->enum('fertilizer', ['-','Kimia', 'Non-Kimia']);
            $table->enum('pesticide', ['-','Kimia', 'Non-Kimia']);
            $table->string('access_to_water_sources')->nullable();
            $table->string('access_to_lahan')->nullable();
            $table->enum('potency', ['-','Konservasi', 'Agroforestry Intensitas Rendah', 'Agroforestry Intensitas Tinggi']);
            $table->string('barcode')->default('-');
            $table->boolean('is_dell')->default(0);
            $table->boolean('complete_data')->default(0);
            $table->boolean('approve')->default(0);
        });

        Schema::create('lahan_photos', function (Blueprint $table){
            $table->bigIncrements('id');
            $table->string('lahan_no');
            $table->foreign('lahan_no')->references('lahan_no')->on('lahans');
            $table->string('filename');
            $table->string('path');
            $table->timestamps();
        });

        Schema::create('lahan_videos', function (Blueprint $table){
            $table->bigIncrements('id');
            $table->string('lahan_no');
            $table->foreign('lahan_no')->references('lahan_no')->on('lahans');
            $table->string('filename');
            $table->string('extension');
            $table->string('path');
            $table->string('mime_type');
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
        Schema::dropIfExists('lahan_photos');
        Schema::dropIfExists('lahan_videos');
        Schema::dropIfExists('lahans');
    }
}
