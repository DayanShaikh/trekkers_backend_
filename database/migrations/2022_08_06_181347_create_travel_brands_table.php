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
        Schema::create('travel_brands', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('travel_admin_id')->nullable();
            $table->string('brand_name');
            $table->string('email')->unique();
            $table->string('password');
            $table->decimal('commission', 10,2)->nullable();
            $table->unsignedTinyInteger('commission_type')->nullable();
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
        Schema::dropIfExists('travel_brands');
    }
};
