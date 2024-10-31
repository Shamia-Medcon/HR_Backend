<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('track_requests', function (Blueprint $table) {
            $table->id();
            $table->string('status');
            $table->unsignedBigInteger('type_id');
            $table->foreign('type_id')->references('id')->on('leave_types')->cascadeOnDelete();
            $table->unsignedBigInteger('leave_id');
            $table->foreign('leave_id')->references('id')->on('leave_requests')->cascadeOnDelete();
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
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
        Schema::dropIfExists('track_requests');
    }
};
