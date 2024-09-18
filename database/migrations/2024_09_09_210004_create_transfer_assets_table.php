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
        Schema::create('transfer_assets', function (Blueprint $table) {
            $table->bigIncrements('transfer_asset_id');
            $table->unsignedBigInteger('department_id');
            $table->string('registration_number', 250);
            $table->string('transfer_type', 250);
            $table->string('reason', 500);
            $table->unsignedBigInteger('created_by');
            $table->softDeletes();
            $table->timestamps();

            // Foreign key constraints without cascade delete
            $table->foreign('department_id')->references('department_id')->on('departments');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transfer_assets');
    }
};
