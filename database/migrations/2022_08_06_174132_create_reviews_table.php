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
        Schema::create('reviews', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('trip_booking_id')->nullable();
            $table->unsignedBigInteger('fake_trip_booking_id')->nullable();
            $table->date('review_date')->nullable();
            $table->integer('tour_guide_points');
            $table->integer('quality_price_points');
            $table->integer('activities_points');
            $table->text('review_text')->nullable();
            $table->text('feedback_text')->nullable();
            $table->string('review_picture')->nullable();
            $table->boolean('show_client_details')->default(true);           
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
        Schema::dropIfExists('reviews');
    }
};
