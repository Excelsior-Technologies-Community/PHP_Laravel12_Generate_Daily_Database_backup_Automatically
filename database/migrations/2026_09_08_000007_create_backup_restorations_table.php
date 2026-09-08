<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('backup_restorations', function (Blueprint $table) {
            $table->id();
            $table->string('backup_filename');
            $table->string('source_database')->nullable();
            $table->string('target_database')->nullable();
            $table->string('status')->default('pending');
            $table->text('error_message')->nullable();
            $table->unsignedBigInteger('restored_by')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index('backup_filename');
            $table->index('status');
            $table->index('restored_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('backup_restorations');
    }
};
