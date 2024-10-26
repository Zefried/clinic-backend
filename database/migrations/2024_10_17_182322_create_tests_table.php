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
        Schema::create('tests', function (Blueprint $table) {
            $table->id(); 
            $table->string('name')->nullable()->index(); 
            $table->text('description')->nullable(); 
            $table->string('status')->default(true)->index();
            $table->string('disable_status')->nullable()->default(false);
            $table->timestamps(); 

            $table->foreignId('test_category_id')->constrained('test_categories')->index();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tests');
    }
};
