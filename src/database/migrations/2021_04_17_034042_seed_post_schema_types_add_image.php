<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use VCComponent\Laravel\Post\Entities\PostSchemaType;

class SeedPostSchemaTypesAddImage extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        PostSchemaType::insert([
            [
                "name" => "image",
            ]
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }
}
