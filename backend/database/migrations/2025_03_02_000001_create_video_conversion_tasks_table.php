<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('video_conversion_tasks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('input_path');
            $table->string('output_path')->nullable();
            $table->string('status', 20)->default('queued')->index();
            $table->unsignedTinyInteger('progress')->default(0);
            $table->text('error_message')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('video_conversion_tasks');
    }
};
