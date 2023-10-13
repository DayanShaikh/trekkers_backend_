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
        Schema::create('trip_ticket_users', function (Blueprint $table) {
            $table->id();
			$table->bigInteger('trip_ticket_id')->nullable();
			$table->bigInteger('trip_booking_id')->nullable();
            $table->string('ticket_number')->nullable();
			$table->text('notes')->nullable();
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
        Schema::dropIfExists('trip_ticket_users');
    }
};
