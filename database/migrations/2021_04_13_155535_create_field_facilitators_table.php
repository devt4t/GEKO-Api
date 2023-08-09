<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFieldFacilitatorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('field_facilitators', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('ff_no')->unique();
            $table->string('name');
            $table->date('birthday');
            $table->enum('religion', ['islam', 'kristen', 'khatolik', 'hindu', 'buddha', 'konghuchu', 'others']);
            $table->enum('gender',['male','female']);
            $table->string('marrital', 191)->nullable();
            $table->date('join_date')->nullable();
            $table->string('ktp_no');
            $table->string('phone');
            $table->string('address');
            $table->string('village');
            $table->string('kecamatan');
            $table->string('city');
            $table->string('province');
            $table->string('post_code')->nullable();
            $table->string('mu_no');
            $table->string('target_area');
            $table->string('bank_account', 191)->nullable();
            $table->string('bank_branch', 191)->nullable();
            $table->string('bank_name', 191)->nullable();
            $table->string('ff_photo');
            $table->string('ff_photo_path');
            $table->boolean('active');
            $table->integer('user_id');
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
        Schema::dropIfExists('field_facilitators');
    }
}
