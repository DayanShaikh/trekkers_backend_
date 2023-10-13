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
        Schema::create('trip_booking_addons', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('trip_booking_id');
            $table->unsignedBigInteger('location_addon_id');
            $table->date('booking_date')->nullable();
            $table->decimal('amount',10,2)->nullable();
            $table->decimal('amount_paid',10,2)->nullable();
            $table->date('payment_date')->nullable();
            $table->boolean('processed')->default(false);
            $table->text('notes')->nullable();
            $table->string('extra_field_1')->nullable();
            $table->string('extra_field_2')->nullable();
            $table->string('extra_field_3')->nullable();
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
        Schema::dropIfExists('trip_booking_addons');
    }
};
