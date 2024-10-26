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
        Schema::create('test_pagis', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable(); // Name field, nullable
            $table->string('email')->nullable()->unique(); // Email field, nullable and unique
            $table->string('phone')->nullable(); // Phone field, nullable
            $table->string('location')->nullable(); // Location field, nullable
            $table->string('sex')->nullable(); // Sex field, nullable
            $table->timestamps(); // Created and updated timestamps
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('test_pagis');
    }
};
