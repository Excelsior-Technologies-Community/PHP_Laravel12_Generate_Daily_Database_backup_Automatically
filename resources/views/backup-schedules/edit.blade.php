@extends('layouts.app')

@section('title', 'Edit Backup Schedule')

@section('content')
    <div class="hero mt-4">
        <h1 class="fw-bold mb-0">Edit Backup Schedule</h1>
    </div>

    <div class="backup-card mt-4">
        <div class="p-4">
            <form method="POST" action="{{ route('backup-schedules.update', $backupSchedule) }}">
                @csrf @method('PUT')
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Name</label>
                        <input type="text" name="name" class="form-control" required value="{{ old('name', $backupSchedule->name) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Slug</label>
                        <input type="text" name="slug" class="form-control" required value="{{ old('slug', $backupSchedule->slug) }}">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Description</label>
                        <textarea name="description" class="form-control" rows="2">{{ old('description', $backupSchedule->description) }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Cron Expression</label>
                        <input type="text" name="cron_expression" class="form-control" required value="{{ old('cron_expression', $backupSchedule->cron_expression) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Timezone</label>
                        <input type="text" name="timezone" class="form-control" required value="{{ old('timezone', $backupSchedule->timezone) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Compression</label>
                        <select name="compression_type" class="form-select">
                            <option value="">None</option>
                            <option value="gzip" @selected(old('compression_type', $backupSchedule->compression_type) === 'gzip')>GZIP</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Encryption</label>
                        <select name="encryption_type" class="form-select">
                            <option value="">None</option>
                            <option value="AES-256-CBC" @selected(old('encryption_type', $backupSchedule->encryption_type) === 'AES-256-CBC')>AES-256-CBC</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Retention Days</label>
                        <input type="number" name="retention_days" class="form-control" value="{{ old('retention_days', $backupSchedule->retention_days) }}" min="1">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Cloud Provider</label>
                        <select name="cloud_provider" class="form-select">
                            <option value="">None</option>
                            <option value="s3" @selected(old('cloud_provider', $backupSchedule->cloud_provider) === 's3')>AWS S3</option>
                            <option value="google_drive" @selected(old('cloud_provider', $backupSchedule->cloud_provider) === 'google_drive')>Google Drive</option>
                            <option value="dropbox" @selected(old('cloud_provider', $backupSchedule->cloud_provider) === 'dropbox')>Dropbox</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <div class="form-check mt-4">
                            <input type="checkbox" name="auto_upload" value="1" class="form-check-input" id="autoUpload" {{ old('auto_upload', $backupSchedule->auto_upload) ? 'checked' : '' }}>
                            <label class="form-check-label" for="autoUpload">Auto Upload</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-check mt-4">
                            <input type="checkbox" name="auto_cleanup" value="1" class="form-check-input" id="autoCleanup" {{ old('auto_cleanup', $backupSchedule->auto_cleanup) ? 'checked' : '' }}>
                            <label class="form-check-label" for="autoCleanup">Auto Cleanup</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-check mt-4">
                            <input type="checkbox" name="auto_verify" value="1" class="form-check-input" id="autoVerify" {{ old('auto_verify', $backupSchedule->auto_verify) ? 'checked' : '' }}>
                            <label class="form-check-label" for="autoVerify">Auto Verify</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-check mt-4">
                            <input type="checkbox" name="is_active" value="1" class="form-check-input" id="isActive" {{ old('is_active', $backupSchedule->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label" for="isActive">Active</label>
                        </div>
                    </div>
                </div>
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle me-1"></i> Update Schedule</button>
                    <a href="{{ route('backup-schedules.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection
