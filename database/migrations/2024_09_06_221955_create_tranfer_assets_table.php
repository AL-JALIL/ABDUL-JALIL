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
        Schema::create('tranfer_assets', function (Blueprint $table) {
            $table->bigIncrements('reason');
            $table->string('registration_No');           
            $table->string('depertment_Id');
            $table->string('tranfer_Type');
            $table->unsignedBigInteger('chalenge_id');
            $table->unsignedBigInteger('created_by');
            $table->softDeletes();
            $table->timestamps();

            /**$table->foreign('chalenge_id')->references('chalenge_id')->on('chalenges');*/
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tranfer_assets');
    }
};
