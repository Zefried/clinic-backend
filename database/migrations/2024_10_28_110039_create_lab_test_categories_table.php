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
        Schema::create('lab_test_categories', function (Blueprint $table) {
            $table->id();

            $table->string('test_category_id')->nullable();
            $table->unsignedBigInteger('lab_id')->nullable()->index(); 
            $table->string('lab_name')->nullable()->index();
            $table->string('disable_status')->default(false);
    
            $table->timestamps();
    
            // Define the foreign key relationship
            $table->foreign('lab_id')->references('id')->on('lab_models');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lab_test_categories');
    }
};
