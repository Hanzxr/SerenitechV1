<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('booking_requests', function (Blueprint $table) {
        $table->dateTime('rescheduled_time')->nullable(); // suggested by admin
        $table->enum('reschedule_status', ['none','requested','accepted','declined'])->default('none');
        $table->text('reschedule_reason')->nullable(); // student decline reason
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('booking_requests', function (Blueprint $table) {
            //
        });
    }
};
