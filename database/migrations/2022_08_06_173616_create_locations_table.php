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
        Schema::create('locations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('destination_id')->nullable();
            $table->string('title');
            $table->text('trip_letter')->nullable();
            $table->unsignedTinyInteger('show_trip_letter')->default(1);
			$table->decimal('trip_fee', 10,2)->nullable();
            $table->string('travel_time')->nullable();
            $table->string('upsell_email_title')->nullable();
            $table->text('upsell_email_content')->nullable();
            $table->string('upsell_email_title2')->nullable();
            $table->text('upsell_email_content2')->nullable();
            $table->unsignedTinyInteger('has_flight')->default(0);
            $table->string('icons', 1000)->nullable();            
            $table->unsignedTinyInteger('require_passport_details')->default(0);         
            $table->unsignedBigInteger('trip_level')->nullable();
            $table->string('included', 500)->nullable();
            $table->text('travel_information')->nullable();
            $table->text('program_details')->nullable();
            $table->text('packing_list')->nullable();
            $table->text('faqs')->nullable();
            $table->text('faqs_new')->nullable();
            $table->text('reviews')->nullable();
            $table->string('review_text', 1000)->nullable();
            $table->string('listing_title')->nullable();
            $table->text('listing_text')->nullable();
            $table->string('listing_image')->nullable();
			$table->text('marketing_text')->nullable();
            $table->string('excursions')->nullable();
            $table->integer('combination')->nullable();
            $table->integer('flight')->nullable();
            $table->string('meals')->nullable();
            $table->string('min_people')->nullable();
            $table->string('baggage')->nullable();
            $table->unsignedBigInteger('sortorder')->nullable();
			$table->text('dropbox_link')->nullable();
			$table->text('dropbox_link_gids')->nullable();
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
        Schema::dropIfExists('locations');
    }
};
