<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLinksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('links', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('site_id')->unsigned();
            $table->string('url');
            $table->boolean('is_parsed');
            $table->text('error')->nullable();
            $table->timestamps();
            $table->foreign('site_id')->references('id')->on('sites');
        });

        Schema::create('link_link', function (Blueprint $table) {
            $table->bigInteger('source_link')->unsigned()->index();
            $table->bigInteger('target_link')->unsigned()->index();
            $table->timestamps();
            $table->foreign('source_link')->references('id')->on('links');
            $table->foreign('target_link')->references('id')->on('links');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('link_link');
        Schema::dropIfExists('links');
    }
}
