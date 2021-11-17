<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use VCComponent\Laravel\Post\Entities\PostSchemaType;

class AddPostSchemaAudioVideoTypes extends Migration
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
                "name" => "audio",
            ],
            [
                "name" => "video",
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
        //
    }
}
