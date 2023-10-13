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
        Schema::create('support_articles', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('support_category_id')->nullable();
            $table->string('title');
            $table->text('excerpt')->nullable();
            $table->date('date')->nullable();
            $table->string('time')->nullable();
            $table->integer('sortorder')->nullable();
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
        Schema::dropIfExists('support_articles');
    }
};
