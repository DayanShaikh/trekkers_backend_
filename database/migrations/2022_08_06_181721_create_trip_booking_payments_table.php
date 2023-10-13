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
        Schema::create('trip_booking_payments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('trip_booking_id')->nullable();
            $table->string('payment_type');
            $table->date('payment_date')->nullable();
            $table->decimal('amount',10,2)->nullable();
            $table->string('transaction_reference')->nullable();
            $table->text('details')->nullable();
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
        Schema::dropIfExists('trip_booking_payments');
    }
};
