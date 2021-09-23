<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePostMetaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('post_meta', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('post_id');
            $table->string('key');
            $table->longText('value')->nullable();
            $table->timestamps();
        });

        Schema::table('post_meta', function (Blueprint $table) {
            $table->foreign('post_id')->references('id')->on('posts')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('post_meta', function (Blueprint $table) {
            $table->dropForeign('post_meta_post_id_foreign');
        });

        Schema::dropIfExists('post_meta');
    }
}
