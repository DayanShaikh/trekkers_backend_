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
        Schema::create('config_variables', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('config_page_id')->nullable();
            $table->unsignedTinyInteger('input_type')->nullable();
            $table->string('name');
            $table->string('notes', 1000)->nullable();
            $table->text('options')->nullable();
            $table->string('config_key');
            $table->text('value')->nullable();
            $table->unsignedInteger('sortorder')->nullable();
            $table->boolean('status')->default(true);
			$table->boolean('autoload')->default(false);
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
        Schema::dropIfExists('config_variables');
    }
};
