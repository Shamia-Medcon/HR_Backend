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
        Schema::table('leave_requests', function (Blueprint $table) {
//            $table->text("note")->after('status')->nullable();
//            $table->string("phone")->after('status')->nullable();
//            $table->string("address")->after('status')->nullable();
//            $table->boolean("inside_country")->after('status')->default(false);
//            $table->string("country")->after('status')->nullable();
//            $table->string("name_of_ken")->after('status')->nullable();
//            $table->string("phone_of_ken")->after('status')->nullable();
//            $table->enum("ken_relation", ["friend", "relative"])->after('status')->default("relative");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            //
        });
    }
};
