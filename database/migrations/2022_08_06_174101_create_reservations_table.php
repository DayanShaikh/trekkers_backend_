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
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('trip_id')->nullable();
            $table->unsignedBigInteger('trip_booking_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('child_firstname')->nullable();
            $table->string('child_lastname')->nullable();
            $table->boolean('gender')->nullable();
            $table->date('child_dob')->nullable();
            $table->string('parent_name')->nullable();
            $table->string('parent_email')->nullable();
            $table->string('email');
            $table->text('address')->nullable();
            $table->string('house_number')->nullable();
            $table->string('city')->nullable();
            $table->string('postcode')->nullable();
            $table->string('telephone')->nullable();
            $table->string('cellphone')->nullable();
            $table->string('whatsapp_number')->nullable();
            $table->unsignedBigInteger('location_pickup_id')->nullable();
            $table->text('child_diet')->nullable();
            $table->text('child_medication')->nullable();
            $table->text('about_child')->nullable();
            $table->date('date_added')->nullable();
            $table->boolean('can_drive')->nullable();
            $table->boolean('have_driving_license')->nullable();
            $table->boolean('have_creditcard')->nullable();
            $table->decimal('trip_fee', 10,2)->nullable();
            $table->decimal('total_amount', 10,2)->nullable();
            $table->decimal('paid_amount', 10,2)->nullable();
            $table->boolean('deleted')->default(false);
            $table->boolean('payment_reminder_email_sent')->default(false);
            $table->boolean('email_sent')->default(false);
            $table->boolean('login_reminder_email_sent')->default(false);
            $table->boolean('upsell_email_sent')->default(false);
            $table->boolean('deposit_reminder_email_sent')->default(false);
            $table->string('display_name')->nullable();
            $table->string('additional_address')->nullable();
            $table->string('contact_person_name')->nullable();
            $table->string('contact_person_extra_name')->nullable();
            $table->string('contact_person_extra_cellphone')->nullable();
            $table->decimal('reservation_fees', 10,2)->nullable();
            $table->date('reservation_fees_paid_at')->nullable();
            $table->unsignedTinyInteger('reservation_fees_payment_type')->nullable();
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
        Schema::dropIfExists('reservations');
    }
};
