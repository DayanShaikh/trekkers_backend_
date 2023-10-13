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
        Schema::create('trip_bookings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('trip_id');
            $table->unsignedBigInteger('user_id');
            $table->string('child_firstname')->nullable();
            $table->string('child_lastname')->nullable();
            $table->boolean('gender')->default(false);
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
            $table->unsignedTinyInteger('can_drive')->nullable();
            $table->unsignedTinyInteger('have_driving_license')->nullable();
            $table->unsignedTinyInteger('have_creditcard')->nullable();
            $table->decimal('trip_fee', 10,2)->nullable();
            $table->string('insurance', 500)->nullable();
            $table->decimal('cancellation_insurance', 10,2)->nullable();
            $table->decimal('travel_insurance', 10,2)->nullable();
            $table->string('cancellation_policy_number')->nullable();
            $table->string('travel_policy_number')->nullable();
            $table->decimal('survival_adventure_insurance', 10,2)->nullable();
            $table->decimal('insurance_admin_charges', 10,2)->nullable();
            $table->decimal('nature_disaster_insurance', 10,2)->nullable();
            $table->decimal('sgr_contribution', 10,2)->nullable();        
            $table->unsignedTinyInteger('insurnace_question_1')->nullable();
            $table->unsignedTinyInteger('insurnace_question_2')->nullable();
            $table->decimal('total_amount', 10,2)->nullable();
            $table->decimal('paid_amount', 10,2)->nullable();
            $table->boolean('deleted')->default(false);
            $table->boolean('payment_reminder_email_sent')->default(false);
            $table->tinyInteger('total_reminder_sent')->default(0);
            $table->boolean('email_sent')->default(false);
            $table->boolean('login_reminder_email_sent')->default(false);
            $table->boolean('upsell_email_sent')->default(false);
            $table->boolean('deposit_reminder_email_sent')->default(false);
            $table->boolean('passport_reminder_email_sent')->default(false);
            $table->string('display_name')->nullable();
            $table->string('additional_address')->nullable();
            $table->string('contact_person_name')->nullable();
            $table->string('contact_person_extra_name')->nullable();
            $table->string('contact_person_extra_cellphone')->nullable();
            $table->string('travel_agent_email')->nullable();
            $table->decimal('commission', 10,2)->nullable();
            $table->unsignedTinyInteger('covid_option')->nullable();
            $table->string('account_name')->nullable();
            $table->string('account_number')->nullable();
            $table->boolean('phone_reminder_email_sent')->default(false);
			$table->unsignedTinyInteger('country')->nullable();
			$table->string('invoice_number')->nullable();
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
        Schema::dropIfExists('trip_bookings');
    }
};
