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
        Schema::create('responsible_persons', function (Blueprint $table) {
            $table->bigIncrements('responsible_person_id');
            $table->string('payroll', 100);
            $table->string('registration_number', 250);
            $table->date('date');
            $table->string('status', 100);
            $table->unsignedBigInteger('created_by');
            $table->softDeletes();
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    
     public function down(): void
     {
         Schema::dropIfExists('responsible_persons');
     }
};
