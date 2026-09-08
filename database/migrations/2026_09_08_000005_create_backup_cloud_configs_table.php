<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('backup_cloud_configs', function (Blueprint $table) {
            $table->id();
            $table->string('provider');
            $table->string('name')->nullable();
            $table->json('credentials')->nullable();
            $table->string('bucket')->nullable();
            $table->string('region')->nullable();
            $table->string('path')->nullable();
            $table->string('endpoint')->nullable();
            $table->boolean('use_path_style')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('provider');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('backup_cloud_configs');
    }
};
