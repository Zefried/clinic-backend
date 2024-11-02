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
        Schema::create('lab_tests', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('lab_test_id')->nullable()->index();
            $table->unsignedBigInteger('lab_test_category_id')->nullable()->index();
            $table->unsignedBigInteger('lab_id')->nullable()->index(); 

            $table->string('lab_test_name')->nullable();
            $table->string('lab_name')->nullable()->index();
            $table->string('disable_status')->default(false);

            $table->timestamps();

            $table->foreign('lab_id')->references('id')->on('lab_models');
            $table->foreign('lab_test_category_id')->references('id')->on('lab_test_categories');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lab_tests');
    }
};
