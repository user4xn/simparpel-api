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
        Schema::create('ships', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('device_id');
            $table->string('firebase_token');
            $table->string('long');
            $table->string('lat');
            $table->enum('status', ['checkin', 'checkout', 'out of scoope'])->nullable();
            $table->tinyInteger('harbour_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ships');
    }
};
