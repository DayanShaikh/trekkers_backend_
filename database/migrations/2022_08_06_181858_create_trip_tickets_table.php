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
        Schema::create('trip_tickets', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('trip_id')->nullable();
            $table->unsignedBigInteger('airline_id')->nullable();
            $table->unsignedBigInteger('connecting_flight')->nullable();
            $table->unsignedTinyInteger('type')->nullable();
            $table->date('datum')->nullable();
            $table->string('vluchtnummer')->nullable();
            $table->string('van')->nullable();
            $table->string('naar')->nullable();
            $table->string('vertrek')->nullable();
            $table->string('ankomst')->nullable();
            $table->unsignedInteger('sortorder')->nullable();
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
        Schema::dropIfExists('trip_tickets');
    }
};
