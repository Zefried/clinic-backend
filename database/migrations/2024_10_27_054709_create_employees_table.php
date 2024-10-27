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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
                        
                $table->string('name')->nullable();
                $table->string('phone')->unique()->nullable();
                $table->string('email')->unique()->nullable();
                $table->string('role')->nullable();
                $table->unsignedBigInteger('lab_id')->nullable();
                $table->string('lab_name')->nullable();
                $table->string('lab_location')->nullable();
                $table->string('disable_status')->nullable()->default(false); 

                $table->index('name');
                $table->index('phone');

                $table->foreign('lab_id')->references('id')->on('lab_models');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
