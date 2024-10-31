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
        Schema::create('leave_requests', function (Blueprint $table) {
            $table->id();
            $table->boolean('priority')->default(false);
            $table->string('leave_time');//range just like 20 Nov 2023 - 16 Dec 2023
            $table->unsignedBigInteger('type_id');
            $table->foreign('type_id')->on('leave_types')->references('id')->cascadeOnDelete();
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->on("users")->references("id")->cascadeOnDelete();
            $table->unsignedBigInteger('manager_id')->nullable();
            $table->foreign('manager_id')->on("users")->references("id")->cascadeOnDelete();
            $table->enum('status', ["pending", "approved", "rejected", "cancelled"]);
            $table->boolean('ticket_request')->default(false);
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
        Schema::dropIfExists('leave_requests');
    }
};
