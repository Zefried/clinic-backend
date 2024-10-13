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
        Schema::create('doctors_user_data', function (Blueprint $table) {
            $table->id();
            
            // Fields specific to doctor data
            $table->string('name')->nullable(); // Doctor's name
            $table->unsignedBigInteger('user_id')->nullable(); // foreign id
            $table->integer('age')->nullable(); // Doctor's age
            $table->string('user_type')->nullable();
            $table->string('sex')->nullable(); // Doctor's sex
            $table->string('relativeName')->nullable(); // Relative name (Father, Mother, Spouse)
            $table->bigInteger('phone')->unique()->nullable(); // Phone number
            $table->string('email')->unique()->nullable(); // Email (unique)
            $table->string('registrationNo')->nullable(); // Registration number
            $table->string('village')->nullable(); // Village
            $table->string('po')->nullable(); // Post Office
            $table->string('ps')->nullable(); // Police Station
            $table->string('pin')->nullable(); // PIN code
            $table->string('district')->nullable(); // District
            $table->string('buildingNo')->nullable(); // Building number
            $table->string('landmark')->nullable(); // Landmark
            $table->string('workDistrict')->nullable(); // Work district
            $table->string('state')->nullable(); // State
            $table->string('designation')->nullable(); // Default designation is 'doctor'
            $table->string('unique_user_id')->unique()->nullable(); // Unique user ID
            $table->string('account_request')->nullable()->default(false); // Default designation is 'doctor'
            
            // Adding indexes for performance
            $table->index('email'); // Index for email
            $table->index('phone'); // Index for phone
            $table->index('unique_user_id'); // Index for unique_user_id
            
            // Foreign key constraint
            $table->foreign('user_id')->references('id')->on('users');
            
            $table->timestamps();
            });
            
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('doctors_user_data');
    }
};
