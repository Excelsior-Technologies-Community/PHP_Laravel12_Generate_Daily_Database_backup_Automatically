<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('backup_logs', function (Blueprint $table) {
            $table->string('compression_type')->nullable()->after('checksum');
            $table->string('encryption_type')->nullable()->after('compression_type');
            $table->string('cloud_provider')->nullable()->after('encryption_type');
            $table->string('cloud_path')->nullable()->after('cloud_provider');
            $table->boolean('is_duplicate')->default(false)->after('cloud_path');
            $table->string('original_filename')->nullable()->after('is_duplicate');
            $table->unsignedBigInteger('template_id')->nullable()->after('original_filename');
            $table->unsignedBigInteger('database_connection_id')->nullable()->after('template_id');
            $table->timestamp('verified_at')->nullable()->after('message');
            $table->timestamp('uploaded_at')->nullable()->after('verified_at');

            $table->index('compression_type');
            $table->index('cloud_provider');
            $table->index('template_id');
            $table->index('database_connection_id');
        });
    }

    public function down(): void
    {
        Schema::table('backup_logs', function (Blueprint $table) {
            $table->dropColumn([
                'compression_type',
                'encryption_type',
                'cloud_provider',
                'cloud_path',
                'is_duplicate',
                'original_filename',
                'template_id',
                'database_connection_id',
                'verified_at',
                'uploaded_at',
            ]);
        });
    }
};
