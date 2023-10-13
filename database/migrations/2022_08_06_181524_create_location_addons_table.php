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
        Schema::create('location_addons', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('location_id')->nullable();
            $table->string('title');
            $table->string('image')->nullable();
            $table->string('mobile_image')->nullable();
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->boolean('is_public')->default(true);
            $table->boolean('hide_payment')->default(false);
            $table->integer('sortorder')->nullable();
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
        Schema::dropIfExists('location_addons');
    }
};
