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
        Schema::create('challenges', function (Blueprint $table) {
            $table->bigIncrements('chalenge_id');  
            $table->uuid('uuid'); 
            $table->unsignedBigInteger('user_id');  
            $table->string('challenge_title');
            $table->unsignedBigInteger('department_id');       
            $table->string('description');
            $table->string('challenge_file')->nullable();
            $table->string('status');
            $table->softDeletes();
            $table->timestamps();
            
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('department_id')->references('department_id')->on('departments');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chalenges');
    }
};
