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
        Schema::create('chalenges', function (Blueprint $table) {
            $table->bigIncrements('chalenge_id');   
            $table->unsignedBigInteger('user_id');       
            $table->string('chalenges');
            $table->string('chalenge_file');
            $table->string('status');
            $table->unsignedBigInteger('created_by');
            $table->softDeletes();
            $table->timestamps();
            
            $table->foreign('user_id')->references('id')->on('users');

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
