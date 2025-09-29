<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media_usages', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('media_item_id')->constrained('media_items')->onDelete('cascade');
            $table->string('model_type');
            $table->string('model_id');
            $table->string('field_key');
            $table->string('group')->nullable();
            $table->unsignedInteger('position')->nullable();
            $table->timestamps();

            $table->unique(['media_item_id', 'model_type', 'model_id', 'field_key', 'position'], 'media_usage_unique');
            $table->index(['model_type', 'model_id']);
            $table->index('field_key');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media_usages');
    }
};
