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
        Schema::create('patient_location__counts', function (Blueprint $table) {
            
            $table->id();
            $table->unsignedBigInteger('location_id')->nullable()->index();
            $table->string('patient_card_id')->nullable();
            $table->unsignedBigInteger('patient_id')->nullable()->index();
            $table->string('associated_user_id')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patient_location__counts');
    }
};
