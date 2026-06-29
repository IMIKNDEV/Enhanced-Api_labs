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
       Schema::create('posts', function (Blueprint $table) {
        $table->id();
        $table->foreignId('raw_content_id')->constrained()->cascadeOnDelete();
        $table->string('hook', 280);
        $table->json('body_points');
        $table->integer('technical_readability_score')->nullable();
        $table->json('suggested_hashtags')->nullable();
        $table->text('tone_compliance_justification')->nullable();
        $table->json('payload_brut')->nullable();
        $table->string('statut_publication')->default('draft');
        $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
