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
        Schema::create('destinations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('title');
            $table->string('iso_code')->nullable();
            $table->decimal('travel_insurance_fees', 10,2)->nullable();
            $table->boolean('is_survival_adventure_insurance_active')->default(true);
			$table->string('intro_title')->nullable();
			$table->text('intro_text')->nullable();
			$table->text('intro_video')->nullable();
			$table->text('video_text')->nullable();
			$table->unsignedBigInteger('header_video_id')->nullable();
			$table->string('trip_title')->nullable();
			$table->string('other_trip_title')->nullable();
			$table->unsignedTinyInteger('trip_toggle')->nullable();
			$table->string('thumb_image')->nullable();
            $table->unsignedInteger('sortorder')->nullable();
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
        Schema::dropIfExists('destinations');
    }
};
