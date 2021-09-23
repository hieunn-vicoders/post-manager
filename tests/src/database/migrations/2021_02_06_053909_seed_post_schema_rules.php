<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use VCComponent\Laravel\Post\Entities\PostSchemaRule;

class SeedPostSchemaRules extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        PostSchemaRule::insert([
            [
                "name" => "E-mail",
            ],
            [
                "name" => "Date",
            ],
            [
                "name" => "Nullable",
            ],
            [
                "name" => "File",
            ],
            [
                "name" => "Required",
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
