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
        Schema::create('trust_checks', function (Blueprint $table) {
            $table->id();
            $table->string('phone_number');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->boolean('sim_swapped')->default(false);
            $table->string('sim_swap_last_changed')->nullable();
            $table->boolean('device_known')->default(false);
            $table->string('device_id')->nullable();
            $table->boolean('location_consistent')->default(true);
            $table->string('location_country')->nullable();
            $table->string('location_city')->nullable();
            $table->integer('trust_score')->nullable();
            $table->string('decision')->nullable();
            $table->text('agent_reasoning')->nullable();
            $table->string('device_label')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trust_checks');
    }
};