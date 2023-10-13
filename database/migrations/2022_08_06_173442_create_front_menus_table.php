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
        Schema::create('front_menus', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedTinyInteger('position')->nullable();
            $table->string('title');
            $table->string('url', 300);
            $table->string('image')->nullable();
            $table->unsignedBigInteger('parent_id')->default(0);
            $table->integer('sortorder')->nullable();
            $table->boolean('is_default')->default(false);
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
        Schema::dropIfExists('front_menus');
    }
};
