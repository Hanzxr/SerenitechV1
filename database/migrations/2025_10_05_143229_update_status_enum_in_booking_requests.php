<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB; // Import DB facade

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Modify the 'status' column to add 'cancelled'
        DB::statement("ALTER TABLE booking_requests MODIFY COLUMN status ENUM('pending', 'approved', 'rejected', 'cancelled') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert the 'status' column to remove 'cancelled'
        DB::statement("ALTER TABLE booking_requests MODIFY COLUMN status ENUM('pending', 'approved', 'rejected') NOT NULL");
    }
};
