<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Create documents table to store uploaded document information.
     * This table maintains file metadata, processing status, and relationships to users.
     */
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // File information
            $table->string('original_filename');
            $table->string('stored_filename');
            $table->string('file_path');
            $table->string('mime_type');
            $table->unsignedBigInteger('file_size'); // in bytes
            $table->string('file_hash')->unique(); // for duplicate detection
            
            // Document metadata
            $table->text('title')->nullable();
            $table->text('description')->nullable();
            $table->json('metadata')->nullable(); // for storing additional file metadata
            
            // Processing status
            $table->enum('status', [
                'uploaded',
                'processing',
                'completed',
                'failed',
                'archived'
            ])->default('uploaded');
            
            $table->text('processing_error')->nullable();
            $table->timestamp('processed_at')->nullable();
            
            // Content extraction
            $table->longText('extracted_content')->nullable();
            $table->json('content_statistics')->nullable(); // word count, pages, etc.
            
            // Soft deletes for data retention
            $table->softDeletes();
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['user_id', 'status']);
            $table->index(['status', 'created_at']);
            $table->index('file_hash');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};