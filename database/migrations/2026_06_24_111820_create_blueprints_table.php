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
        Schema::create('blueprints', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->cascadeOnDelete();
        $table->string('name');
        $table->string('tone');
        $table->unsignedBigInteger('max_hashtag')->default(1);
        $table->unsignedBigInteger('max_characters')->default(280);
        $table->string('banned_word')->nullable();
        $table->text('extra_rules')->nullable();
        $table->timestamps();
    });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blueprints');
    }
};
