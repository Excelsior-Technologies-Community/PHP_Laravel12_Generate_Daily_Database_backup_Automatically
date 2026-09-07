<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('backup_logs', function (Blueprint $table) {
            $table->id();

            $table->string('filename')->nullable();

            $table->enum('status', [
                'success',
                'failed',
            ]);

            $table->unsignedBigInteger('size_bytes')->default(0);

            $table->string('checksum', 64)->nullable();

            $table->text('message')->nullable();

            $table->timestamps();

            $table->index('status');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('backup_logs');
    }
};