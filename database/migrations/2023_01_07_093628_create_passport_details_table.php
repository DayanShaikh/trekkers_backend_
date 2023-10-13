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
        Schema::create('passport_details', function (Blueprint $table) {
            $table->id();
			$table->unsignedBigInteger('trip_booking_id')->nullable();
            $table->string('document_number')->nullable();
			$table->date('issue_date')->nullable();
			$table->date('expiry_date')->nullable();
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
        Schema::dropIfExists('passport_details');
    }
};
