<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateActivitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('activities', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('activity_no')->unique();
            $table->enum('activity_type', ['planning', 'visit', 'distribution', 'realitation', 'monitoring']);
            $table->date('activity_date');
            $table->string('field_facilitator')->nullable();
            $table->foreign('field_facilitator')->references('ff_no')->on('field_facilitators');
            $table->string('lahan_no')->nullable();
            $table->foreign('lahan_no')->references('lahan_no')->on('lahans');
            $table->integer('total_trees');
            $table->string('programs');
            $table->string('activity_description')->nullable();
            $table->integer('user_id');            
            $table->timestamps();
            $table->integer('verification_code')->nullable();
            $table->foreign('verification_code')->references('verification_code')->on('verifications');
        });

        Schema::create('activity_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('activity_no')->nullable();
            $table->foreign('activity_no')->references('activity_no')->on('activities');
            $table->string('tree_code')->nullable();
            $table->foreign('tree_code')->references('tree_code')->on('trees');
            $table->integer('amount');
            $table->string('unit')->default('pcs');
            $table->date('detail_date');
            $table->enum('tree_status', ['-','dead', 'life']);
            $table->integer('growth_percentage');
            $table->integer('diameter');
            $table->integer('high');
            $table->boolean('polybags')->default(0);          
            $table->timestamps();
            $table->string('list_of_things');  
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('activity_details');
        Schema::dropIfExists('activities');
    }
}
