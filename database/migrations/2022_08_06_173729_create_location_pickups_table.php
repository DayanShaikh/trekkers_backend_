<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('location_pickups', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('location_id')->nullable();
            $table->string('place');
            $table->string('time');
            $table->string('spot');
            $table->integer('sortorder')->nullable();
            $table->boolean('status')->default(true);
            $table->integer('creater_id')->nullable();
            $table->softDeletes();
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
        Schema::dropIfExists('location_pickups');
    }
};
