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
        Schema::create('lessons', function (Blueprint $table) {
            $table->id();
			$table->unsignedBigInteger('course_id')->nullable();
			$table->string('title');
			$table->text('small_description')->nullable();
			$table->text('details')->nullable();
			$table->string('duration')->nullable();
			$table->text('intro_video')->nullable();
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
        Schema::dropIfExists('lessons');
    }
};
