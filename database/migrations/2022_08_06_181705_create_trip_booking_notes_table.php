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
        Schema::create('trip_booking_notes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('trip_booking_id')->nullable();
            $table->text('notes');
			$table->boolean('is_log')->default(false);
            $table->boolean('status')->default(true);
            $table->integer('creater_id')->nullable();
			$table->boolean('is_publish')->default(false);
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
        Schema::dropIfExists('trip_booking_notes');
    }
};
