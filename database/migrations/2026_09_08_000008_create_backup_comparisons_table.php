<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('backup_comparisons', function (Blueprint $table) {
            $table->id();
            $table->string('backup_a_filename');
            $table->string('backup_b_filename');
            $table->json('result')->nullable();
            $table->unsignedBigInteger('compared_by')->nullable();
            $table->timestamp('compared_at')->nullable();
            $table->timestamps();

            $table->index('backup_a_filename');
            $table->index('backup_b_filename');
            $table->index('compared_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('backup_comparisons');
    }
};
