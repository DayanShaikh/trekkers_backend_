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
        Schema::create('trip_booking_extra_insurances', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('trip_booking_id')->nullable();
            $table->date('date')->nullable();
            $table->text('insurance')->nullable();
            $table->decimal('survival_adventure_insurance', 10, 2)->default(0);
            $table->decimal('travel_insurance', 10, 2)->default(0);
            $table->decimal('insurance_admin_charges', 10, 2)->default(0);
            $table->boolean('is_completed')->default(false);
            $table->date('payment_date')->nullable();
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
        Schema::dropIfExists('trip_booking_extra_insurances');
    }
};
