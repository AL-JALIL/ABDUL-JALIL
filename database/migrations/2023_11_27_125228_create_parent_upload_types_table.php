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
        Schema::create('parent_upload_types', function (Blueprint $table) {
            $table->string('parent_upload_type_id')->primary()->uniqid();
            $table->uuid('uuid');
            $table->string('parent_upload_id');           
            $table->string('upload_type_id');            
            $table->unsignedBigInteger('created_by');
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('parent_upload_id')->references('parent_upload_id')->on('parent_uploads');
            $table->foreign('upload_type_id')->references('upload_type_id')->on('upload_types');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parent_upload_types');
    }
};
