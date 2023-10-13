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
        Schema::create('email_template_conditions', function (Blueprint $table) {
            $table->id();
			$table->string('booking_days_before_start_date');
			$table->string('days_after_booking')->nullable();
			$table->string('days_before_departure')->nullable();
			$table->unsignedTinyInteger('type')->nullable();
			$table->unsignedBigInteger('email_template_id')->nullable();
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
        Schema::dropIfExists('email_template_conditions');
    }
};
