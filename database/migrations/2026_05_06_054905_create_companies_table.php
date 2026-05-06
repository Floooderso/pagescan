<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('branche');
            $table->string('stadt');
            $table->string('plz')->nullable();
            $table->string('telefon')->nullable();
            $table->string('email')->nullable();
            $table->string('website_url')->nullable();
            $table->boolean('has_website')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
