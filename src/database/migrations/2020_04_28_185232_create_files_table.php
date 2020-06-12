<?php

use MediaHub\Models\FileModels;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('files', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('guid', 36)->index();
            $table->string('hash', 32)->nullable()->index();
            $table->bigInteger('storage_id');
            $table->string('path')->nullable();
            $table->string('name');
            $table->string('mime_type');
            $table->binary('preview')->comment('image 400x400px')->nullable();
            $table->integer('size');
            $table->bigInteger('user_id')->index();
            $table->enum('status', FileModels::STATUSES)->default(FileModels::STATUS_LOADING);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('files');
    }
}
