<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateManagementUnitsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('managementunits', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('mu_no')->unique();
            $table->string('name');
            $table->boolean('active');
            $table->timestamps();
        });

        Schema::create('target_areas', function (Blueprint $table)
        {
            $table->bigIncrements('id');
            $table->string('area_code')->unique();
            $table->string('name');
            
            $table->string('mu_no')->nullable();
            $table->foreign('mu_no')->references('mu_no')->on('managementunits')->onUpdate('cascade')->onDelete('set null');

            $table->boolean('active');
            $table->string('kab_code')->nullable();
            $table->foreign('kab_code')->references('kab_code')->on('kabupatens')->onUpdate('cascade')->onDelete('set null');

            $table->string('fc_no');
            $table->string('province_code')->nullable();
            $table->foreign('province_code')->references('province_code')->on('provinces')->onUpdate('cascade')->onDelete('set null');
            $table->float('luas');
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
        Schema::dropIfExists('target_areas');
        Schema::dropIfExists('managementunits');
    }
}
