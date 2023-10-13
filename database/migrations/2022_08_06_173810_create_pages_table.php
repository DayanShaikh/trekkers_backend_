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
        Schema::create('pages', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->nullableMorphs('pageable');
            $table->string('page_name')->nullable();
            $table->string('title');
            $table->text('content')->nullable();
            $table->text('highlights')->nullable();
            $table->string('image')->nullable();
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->text('meta_keywords')->nullable();
            $table->string('sitemap_title')->nullable();
            $table->text('sitemap_details')->nullable();
            $table->text('header_details')->nullable();
            $table->integer('show_schema_markup')->default(1);
            $table->string('schema_title')->nullable();
			$table->boolean('show_search_box')->default(false);
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
        Schema::dropIfExists('pages');
    }
};
