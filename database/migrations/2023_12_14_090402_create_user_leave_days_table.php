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
        Schema::create('user_leave_days', function (Blueprint $table) {
            $table->id();
            $table->string('note');
            $table->boolean('status')->default(true);//Active,Inactive
            $table->string('year');
            $table->enum('additional_days', [0, 1, 2, 3, 4, 5])->default(0);
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->on("users")->references("id")->cascadeOnDelete();
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
        Schema::dropIfExists('user_leave_days');
    }
};
