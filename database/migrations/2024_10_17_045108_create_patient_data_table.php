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
        Schema::create('patient_data', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->integer('age')->nullable();
            $table->string('sex')->nullable();
            $table->string('relativeName')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('identityProof')->nullable();
            $table->string('village')->nullable();
            $table->string('po')->nullable(); // Post Office
            $table->string('ps')->nullable(); // Police Station
            $table->string('pin')->nullable(); // Postal Index Number
            $table->string('district')->nullable();
            $table->string('state')->nullable();
            $table->string('unique_patient_id')->nullable();
            $table->string('request_status')->nullable()->default(false);
            $table->string('associated_user_email')->nullable();
            $table->string('associated_user_id')->nullable();
            $table->string('disable_status')->nullable()->default(false);

            // Adding indexes
            $table->index('phone');
            $table->index('unique_patient_id');
            $table->index('associated_user_email');
            $table->index('associated_user_id');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patient_data');
    }
};
