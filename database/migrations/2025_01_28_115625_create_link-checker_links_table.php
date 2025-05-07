<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('link-checker_links', function ($table) {
            $table->id();
            $table->string('app-index')->nullable();
            $table->string('code')->nullable();
            $table->string('url')->nullable();
            $table->string('source')->nullable();
            $table->string('editor')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('link-checker_links');
    }
};
