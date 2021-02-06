<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use VCComponent\Laravel\Post\Entities\PostSchemaType;

class SeedPostSchemaTypes extends Migration
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
                "name" => "text",
            ],
            [
                "name" => "textarea",
            ],
            [
                "name" => "tinyMCE",
            ],
            [
                "name" => "checkbox",
            ],
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
