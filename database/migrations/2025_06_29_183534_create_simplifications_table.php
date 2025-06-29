<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Create simplifications table to store AI-generated cat stories.
     * Each document can have multiple simplifications with different parameters.
     */
    public function up(): void
    {
        Schema::create('simplifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // AI Processing parameters
            $table->string('ai_model')->default('gpt-3.5-turbo');
            $table->enum('complexity_level', ['basic', 'intermediate', 'advanced'])->default('basic');
            $table->json('processing_parameters')->nullable(); // temperature, max_tokens, etc.
            
            // Generated content
            $table->text('simplified_title')->nullable();
            $table->longText('cat_story');
            $table->longText('summary')->nullable();
            $table->json('key_concepts')->nullable(); // extracted key concepts as JSON array
            
            // Processing information
            $table->enum('status', [
                'pending',
                'processing',
                'completed',
                'failed'
            ])->default('pending');
            
            $table->text('processing_error')->nullable();
            $table->timestamp('processed_at')->nullable();
            
            // AI API usage tracking
            $table->unsignedInteger('tokens_used')->nullable();
            $table->decimal('processing_cost', 10, 6)->nullable(); // cost in dollars
            $table->unsignedInteger('processing_time_seconds')->nullable();
            
            // Content quality metrics
            $table->unsignedTinyInteger('readability_score')->nullable(); // 1-10 scale
            $table->json('quality_metrics')->nullable(); // various quality indicators
            
            // User feedback
            $table->boolean('is_favorite')->default(false);
            $table->unsignedTinyInteger('user_rating')->nullable(); // 1-5 stars
            $table->text('user_notes')->nullable();
            
            // Sharing and download tracking
            $table->boolean('is_public')->default(false);
            $table->string('share_token')->unique()->nullable();
            $table->unsignedInteger('download_count')->default(0);
            $table->timestamp('last_downloaded_at')->nullable();
            
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['document_id', 'status']);
            $table->index(['user_id', 'created_at']);
            $table->index(['status', 'created_at']);
            $table->index('share_token');
            $table->index(['is_public', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('simplifications');
    }
};