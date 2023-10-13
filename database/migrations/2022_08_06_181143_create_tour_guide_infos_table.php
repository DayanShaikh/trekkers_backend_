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
        Schema::create('tour_guide_infos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->dateTime('datetime_added')->nullable();
            $table->date('dob')->nullable();
            $table->string('street_name');
            $table->string('house_number');
            $table->string('residence')->nullable();
            $table->string('telephone')->nullable();
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_number')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('id_card_number')->nullable();
            $table->date('expiry_date')->nullable();
            $table->text('availability')->nullable();
            $table->string('licence_image')->nullable();
            $table->string('bank_account_number')->nullable();
            $table->string('email')->nullable();
            $table->string('passport_image')->nullable();
            $table->date('expiry_date_passport')->nullable();
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
        Schema::dropIfExists('tour_guide_infos');
    }
};
