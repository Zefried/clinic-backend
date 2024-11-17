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
        Schema::create('patient_assigned_data', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('patient_id')->nullable();
            $table->string('patient_name')->nullable()->index();
            $table->unsignedBigInteger('lab_id')->nullable();
            $table->string('lab_name')->nullable()->index();
            $table->unsignedBigInteger('employee_id')->nullable();
            $table->string('employee_name')->nullable()->index();
            $table->decimal('final_amount', 10, 2)->nullable();
            $table->decimal('discount', 8, 2)->nullable();
            $table->decimal('final_discount', 8, 2)->nullable();
            $table->unsignedBigInteger('associated_sewek_id')->nullable()->index();
            $table->string('disable_status')->default(false);
            $table->string('doc_path')->nullable();
            $table->json('test_ids')->nullable();
            $table->integer('visit')->default(0);
            $table->string('patient_status')->default('pending');
            $table->timestamps();
            
          
            
            // Foreign key relation to employees table
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('set null');
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patient_assigned_data');
    }
};
