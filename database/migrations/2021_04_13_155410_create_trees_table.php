<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTreesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('trees', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('tree_code')->unique();
            $table->string('tree_name');
            $table->string('scientific_name');
            $table->string('english_name')->nullable();
            $table->string('common_name');
            $table->string('short_information');
            $table->string('description')->nullable();
            $table->string('tree_category');
            $table->string('product_list')->nullable();
            $table->string('estimate_income')->nullable();
            $table->float('co2_capture')->nullable();
            $table->timestamps();
            $table->string('photo1')->nullable();
            $table->string('photo2')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {   
        Schema::dropIfExists('trees');
    }
}
