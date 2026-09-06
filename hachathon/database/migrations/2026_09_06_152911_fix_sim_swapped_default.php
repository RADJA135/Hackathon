<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trust_checks', function (Blueprint $table) {
            // Change sim_swapped from nullable to NOT NULL with default false
            $table->boolean('sim_swapped')->default(false)->change();

            // (Optional) also fix device_known and location_consistent if they cause trouble
            $table->boolean('device_known')->default(false)->change();
            $table->boolean('location_consistent')->default(true)->change(); // true = consistent
        });
    }

    public function down(): void
    {
        Schema::table('trust_checks', function (Blueprint $table) {
            // Revert to nullable (no default)
            $table->boolean('sim_swapped')->nullable()->change();
            $table->boolean('device_known')->nullable()->change();
            $table->boolean('location_consistent')->nullable()->change();
        });
    }
};
