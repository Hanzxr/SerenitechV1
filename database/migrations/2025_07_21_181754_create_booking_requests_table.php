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
        Schema::create('booking_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('counselor_id')->constrained('users')->onDelete('cascade');
            $table->string('course')->nullable();
            $table->integer('age')->nullable();
            $table->string('sex')->nullable();
            $table->string('address')->nullable();
            $table->string('contact')->nullable();
            $table->string('civil_status')->nullable();

            // Emergency Contact
            $table->string('emergency_name')->nullable();
            $table->string('emergency_address')->nullable();
            $table->string('emergency_relationship')->nullable();
            $table->string('emergency_contact')->nullable();
            $table->string('emergency_occupation')->nullable();

            $table->text('reason'); // Reason for counseling
            $table->dateTime('preferred_time');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_requests');
    }
};
