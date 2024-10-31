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
        Schema::table('leave_types', function (Blueprint $table) {
            $table->integer('additional_days_available')->after('ticket_request')->default(0);
            $table->boolean('address_required')->after('ticket_request')->default(0);
            $table->integer('contact_required')->after('ticket_request')->default(0);
            $table->integer('ken_required')->after('ticket_request')->default(0);
            $table->integer('salary_reflectable')->after('ticket_request')->default(0);

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('leave_types', function (Blueprint $table) {
            //
        });
    }
};
