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
        Schema::create('responsible_people', function (Blueprint $table) {
            $table->bigIncrements('payroll');         
            $table->string('registratio_No');
            $table->string('date');
            $table->string('status');
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
        Schema::dropIfExists('responsible_people');
    }
};
