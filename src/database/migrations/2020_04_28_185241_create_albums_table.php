<?php

use MediaHub\Models\AlbumModels;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAlbumsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('albums', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('default')->default(0);
            $table->bigInteger('user_id')->index();
            $table->timestamps();
            $table->softDeletes();
            $table->enum('access', AlbumModels::ACCESSES)->default(AlbumModels::ACCESS_PRIVATE);
            $table->string('url')->index();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('albums');
    }
}
