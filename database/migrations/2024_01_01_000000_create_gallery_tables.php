<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Create folders table first (referenced by media table)
        Schema::create('folders', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('parent_id')->nullable()->constrained('folders')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
        });

        // Create media table
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('original')->nullable();
            $table->string('name')->nullable();
            $table->enum('type', ['image', 'video', 'document', 'audio', 'other'])->default('image');
            $table->string('path')->nullable();
            $table->foreignId('folder_id')->nullable()->constrained('folders')->onDelete('set null');
            $table->string('url')->nullable();
            $table->string('mime_type')->nullable();
            $table->string('extension')->nullable();
            $table->unsignedBigInteger('size')->default(0);
            $table->unsignedBigInteger('width')->default(0);
            $table->unsignedBigInteger('height')->default(0);
            $table->unsignedBigInteger('duration')->default(0);
            $table->softDeletes();
            $table->timestamps();

            // Index
            $table->index(['original', 'type', 'path', 'url']);
        });

        // Create tags table
        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->timestamps();
        });

        // Create media_tag pivot table
        Schema::create('media_tag', function (Blueprint $table) {
            $table->foreignId('media_id')->constrained('media')->onDelete('cascade');
            $table->foreignId('tag_id')->constrained('tags')->onDelete('cascade');
            $table->primary(['media_id', 'tag_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media_tag');
        Schema::dropIfExists('tags');
        Schema::dropIfExists('media');
        Schema::dropIfExists('folders');
    }
};
