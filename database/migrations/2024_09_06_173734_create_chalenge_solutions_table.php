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
        Schema::create('chalenge_solutions', function (Blueprint $table) {
            $table->bigIncrements('user_id');
            $table->string('solution');           
            $table->string('solution_file');
            $table->string('status');
            $table->unsignedBigInteger('chalenge_id');
            $table->unsignedBigInteger('created_by');
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('chalenge_id')->references('chalenge_id')->on('chalenges');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chalenge_solutions');
    }
};
