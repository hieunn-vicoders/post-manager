<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePostSchemasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('post_schemas', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('label');
            $table->unsignedBigInteger('schema_type_id');
            $table->unsignedBigInteger('schema_rule_id');
            $table->string('post_type')->default('posts');
            $table->foreign('schema_type_id')->references('id')->on('post_schema_types');
            $table->foreign('schema_rule_id')->references('id')->on('post_schema_rules');
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
        Schema::dropIfExists('post_schemas');
    }
}
