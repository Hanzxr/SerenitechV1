<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up(): void
{
    Schema::create('video_sessions', function (Blueprint $table) {
        $table->id();
        $table->foreignId('booking_id')->constrained('booking_requests')->onDelete('cascade');
        $table->string('room_name'); // Jitsi room name
        $table->enum('status', ['waiting', 'ongoing', 'ended'])->default('waiting');
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('video_sessions');
    }
};
