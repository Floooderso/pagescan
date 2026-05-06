<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['pending', 'running', 'done', 'failed'])->default('pending');
            $table->integer('http_status')->nullable();
            $table->integer('response_ms')->nullable();
            $table->boolean('has_ssl')->nullable();
            $table->boolean('is_mobile')->nullable();
            $table->string('cms')->nullable();
            $table->boolean('has_contact')->nullable();
            $table->boolean('has_impressum')->nullable();
            $table->boolean('has_tracking')->nullable();
            $table->boolean('has_favicon')->nullable();
            $table->integer('score')->nullable();
            $table->string('error')->nullable();
            $table->timestamp('scanned_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scans');
    }
};
