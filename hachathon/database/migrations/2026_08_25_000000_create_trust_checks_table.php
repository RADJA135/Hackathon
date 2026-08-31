<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trust_checks', function (Blueprint $table) {
            $table->id();

            // who / what triggered this check
            $table->string('phone_number');
            $table->unsignedBigInteger('user_id')->nullable();

            // raw signal results — one column per API, filled in as each teammate's endpoint runs
            $table->boolean('sim_swapped')->nullable();        // Radja's signal
            $table->string('sim_swap_last_changed')->nullable();

            $table->boolean('device_known')->nullable();        // Semsoum's signal
            $table->string('device_id')->nullable();

            $table->boolean('location_consistent')->nullable(); // Haddad's signal
            $table->string('location_country')->nullable();
            $table->string('location_city')->nullable();

            // AI agent's combined output
            $table->unsignedTinyInteger('trust_score')->nullable();   // 0-100
            $table->enum('decision', ['allow', 'warn', 'block'])->nullable();
            $table->text('agent_reasoning')->nullable();       // why the agent decided this

            // request context
            $table->string('device_label')->nullable();         // "iPhone 14 · Algiers" for History display
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trust_checks');
    }
};
