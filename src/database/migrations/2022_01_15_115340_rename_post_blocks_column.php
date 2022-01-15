<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenamePostBlocksColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('post_blocks', function(Blueprint $table) {
            $table->renameColumn('blocks', 'block');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('post_blocks', function(Blueprint $table) {
            $table->renameColumn('block', 'blocks');
        });
    }
}
