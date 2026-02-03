<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gallery_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->string('filename');
            $table->string('original_filename');
            $table->string('mime_type');
            $table->unsignedBigInteger('size'); // bytes
            $table->string('disk')->default('public');
            $table->string('path');
            $table->string('thumbnail_path')->nullable();
            $table->string('medium_path')->nullable();
            $table->json('metadata')->nullable(); // width, height, etc.
            $table->string('folder_date'); // Y-m-d format
            $table->boolean('is_public')->default(true);
            $table->timestamp('uploaded_at');
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('user_id');
            $table->index('folder_date');
            $table->index('created_at');
        });

        Schema::create('gallery_folders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('folder_path')->unique();
            $table->string('folder_date');
            $table->unsignedInteger('image_count')->default(0);
            $table->unsignedBigInteger('total_size')->default(0);
            $table->timestamps();

            $table->index(['user_id', 'folder_date']);
        });

        Schema::create('gallery_tags', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->timestamps();
        });

        Schema::create('gallery_image_tag', function (Blueprint $table) {
            $table->foreignId('gallery_image_id')->constrained('gallery_images')->onDelete('cascade');
            $table->foreignId('gallery_tag_id')->constrained('gallery_tags')->onDelete('cascade');
            $table->primary(['gallery_image_id', 'gallery_tag_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gallery_image_tag');
        Schema::dropIfExists('gallery_tags');
        Schema::dropIfExists('gallery_folders');
        Schema::dropIfExists('gallery_images');
    }
};
