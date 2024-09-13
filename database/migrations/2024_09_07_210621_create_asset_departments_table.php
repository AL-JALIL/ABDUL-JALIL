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
        Schema::create('asset_departments', function (Blueprint $table) {
            $table->bigIncrements('asset_department_id');
            $table->unsignedBigInteger('asset_id');
            $table->unsignedBigInteger('department_id');
            $table->unsignedBigInteger('condition_id');
            $table->date('start_date')->default(DB::raw('CURRENT_DATE'));
            $table->string('registration_number', 250);
            $table->string('status', 250);
            $table->unsignedBigInteger('created_by');
            $table->softDeletes();
            $table->timestamps();

            // Foreign key constraints without cascade delete
            $table->foreign('asset_id')->references('asset_id')->on('assets');
            $table->foreign('department_id')->references('department_id')->on('departments');
            $table->foreign('condition_id')->references('condition_id')->on('conditions');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_department');
    }
};
