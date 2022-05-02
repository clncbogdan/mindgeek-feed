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
        Schema::create('movies', function (Blueprint $table) {
            $table->id();
            $table->string('UUID')->index();
            $table->text('body');
            $table->string('cert');
            $table->integer('duration');
            $table->string('headline');
            $table->string('quote');
            $table->unsignedSmallInteger('rating');
            $table->string('review_author');
            $table->string('sky_go_id');
            $table->string('sky_go_url');
            $table->string('sum');
            $table->text('synopsis');
            $table->string('url');
            $table->timestamp('vw_start_date')->nullable();
            $table->string('vw_wtw');
            $table->timestamp('vw_end_date')->nullable();
            $table->unsignedSmallInteger('year');
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
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('movies');
        Schema::enableForeignKeyConstraints();
    }
};
