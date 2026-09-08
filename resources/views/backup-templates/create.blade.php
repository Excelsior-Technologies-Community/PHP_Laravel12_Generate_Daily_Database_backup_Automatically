@extends('layouts.app')

@section('title', 'Create Backup Template')

@section('content')
    <div class="hero mt-4">
        <h1 class="fw-bold mb-0">Create Backup Template</h1>
    </div>

    <div class="backup-card mt-4">
        <div class="p-4">
            <form method="POST" action="{{ route('backup-templates.store') }}">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Name</label>
                        <input type="text" name="name" class="form-control" required value="{{ old('name') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Slug</label>
                        <input type="text" name="slug" class="form-control" required value="{{ old('slug') }}">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Description</label>
                        <textarea name="description" class="form-control" rows="2">{{ old('description') }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Compression</label>
                        <select name="compression_type" class="form-select">
                            <option value="">None</option>
                            <option value="gzip" @selected(old('compression_type') === 'gzip')>GZIP</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Encryption</label>
                        <select name="encryption_type" class="form-select">
                            <option value="">None</option>
                            <option value="AES-256-CBC" @selected(old('encryption_type') === 'AES-256-CBC')>AES-256-CBC</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Retention Days</label>
                        <input type="number" name="retention_days" class="form-control" value="{{ old('retention_days', 7) }}" min="1">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Cloud Provider</label>
                        <select name="cloud_provider" class="form-select">
                            <option value="">None</option>
                            <option value="s3" @selected(old('cloud_provider') === 's3')>AWS S3</option>
                            <option value="google_drive" @selected(old('cloud_provider') === 'google_drive')>Google Drive</option>
                            <option value="dropbox" @selected(old('cloud_provider') === 'dropbox')>Dropbox</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <div class="form-check mt-4">
                            <input type="checkbox" name="auto_upload" value="1" class="form-check-input" id="autoUpload" {{ old('auto_upload') ? 'checked' : '' }}>
                            <label class="form-check-label" for="autoUpload">Auto Upload</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-check mt-4">
                            <input type="checkbox" name="is_active" value="1" class="form-check-input" id="isActive" {{ old('is_active', true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="isActive">Active</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-check mt-4">
                            <input type="checkbox" name="is_default" value="1" class="form-check-input" id="isDefault" {{ old('is_default') ? 'checked' : '' }}>
                            <label class="form-check-label" for="isDefault">Set as Default</label>
                        </div>
                    </div>
                </div>
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle me-1"></i> Create Template</button>
                    <a href="{{ route('backup-templates.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection
