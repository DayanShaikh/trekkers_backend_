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
        Schema::create('trips', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('location_id');
            $table->unsignedBigInteger('total_space')->nullable();
            $table->boolean('male_female_important')->default(false);
            $table->boolean('show_client_detail')->default(false);
            $table->date('start_date');
            $table->unsignedBigInteger('duration')->nullable();
            $table->decimal('trip_fee', 10,2)->nullable();
            $table->unsignedBigInteger('trip_seats_status')->nullable();
            $table->tinyInteger('is_not_bookable')->default(0);
            $table->boolean('archive')->default(false);
            $table->boolean('is_full')->default(false);
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
        Schema::dropIfExists('trips');
    }
};
