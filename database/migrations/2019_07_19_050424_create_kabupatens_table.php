<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateKabupatensTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('provinces', function (Blueprint $table) {
            $table->increments('id');
            $table->string('province_code')->unique();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('kabupatens', function (Blueprint $table) {
            $table->increments('id');
            $table->string('kabupaten_no')->unique();
            $table->string('name');
            $table->string('province_code')->nullable();
            $table->foreign('province_code')->references('province_code')->on('provinces')->onUpdate('cascade')->onDelete('set null');
            $table->timestamps();
            $table->string('kab_code')->unique();
        });

        Schema::create('kecamatans', function (Blueprint $table) {
            $table->increments('id');
            $table->string('kode_kecamatan')->unique();
            $table->string('name');
            $table->string('kabupaten_no')->nullable();
            $table->foreign('kabupaten_no')->references('kabupaten_no')->on('kabupatens')->onUpdate('cascade')->onDelete('set null');
            $table->timestamps();
        });

        Schema::create('desas', function (Blueprint $table) {
            $table->increments('id');
            $table->string('kode_desa')->unique();
            $table->string('name');
            $table->string('kode_kecamatan')->nullable();
            $table->foreign('kode_kecamatan')->references('kode_kecamatan')->on('kecamatans')->onUpdate('cascade')->onDelete('set null');
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
        Schema::dropIfExists('desas');
        Schema::dropIfExists('kecamatans');
        Schema::dropIfExists('kabupatens');
        Schema::dropIfExists('provinces');
        
    }
}
