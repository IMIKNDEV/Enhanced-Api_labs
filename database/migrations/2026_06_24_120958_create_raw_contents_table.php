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
        Schema::create('raw_contents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->CascadeOnDelete();
            $table->foreignId('blueprint_id')->constrained()->CascadeOnDelete();
            $table->text('body');
            $table->string('status')->default('en_attente');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('raw_contents');
    }
};
