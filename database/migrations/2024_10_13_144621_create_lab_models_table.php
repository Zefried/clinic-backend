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
        Schema::create('lab_models', function (Blueprint $table) {
            $table->id();

            $table->string('name')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('phone')->nullable()->unique();
            $table->string('email')->unique();
            $table->string('registrationNo')->nullable();
            $table->string('buildingNo')->nullable();
            $table->string('landmark')->nullable();
            $table->string('district')->nullable();
            $table->string('state')->nullable();
            $table->string('lab_account_request')->nullable()->default(false);
            $table->string('lab_unique_id')->nullable();
            $table->string('disable_status')->nullable()->default(false);
            
            // Adding indexes
            $table->index('phone');            
            $table->index('email');   
            $table->index('lab_unique_id'); 

            $table->timestamps();
            // Defining the foreign key
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lab_models');
    }
};
