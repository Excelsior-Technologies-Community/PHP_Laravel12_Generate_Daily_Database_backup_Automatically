<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('backup_schedules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('cron_expression')->default('0 0 * * *');
            $table->string('timezone')->default('UTC');
            $table->json('databases')->nullable();
            $table->string('compression_type')->nullable();
            $table->string('encryption_type')->nullable();
            $table->boolean('auto_upload')->default(false);
            $table->string('cloud_provider')->nullable();
            $table->json('cloud_config')->nullable();
            $table->json('notification_channels')->nullable();
            $table->json('webhook_urls')->nullable();
            $table->integer('retention_days')->default(7);
            $table->boolean('auto_cleanup')->default(true);
            $table->boolean('auto_verify')->default(true);
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('template_id')->nullable();
            $table->timestamps();

            $table->index('slug');
            $table->index('is_active');
            $table->index('template_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('backup_schedules');
    }
};
