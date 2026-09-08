@extends('layouts.app')

@section('title', 'Backup Templates')

@section('content')
    <div class="hero mt-4">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="fw-bold mb-3">Backup Templates</h1>
                <p class="mb-0 fs-5">Pre-configured backup settings for quick deployment.</p>
            </div>
            <div class="col-md-4 text-md-end mt-4 mt-md-0">
                <a href="{{ route('backup-templates.create') }}" class="btn btn-light btn-lg fw-semibold"><i class="bi bi-plus-circle me-2"></i> New Template</a>
            </div>
        </div>
    </div>

    <div class="backup-card mt-4">
        <div class="p-4">
            @if($templates->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Compression</th>
                            <th>Encryption</th>
                            <th>Retention</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($templates as $template)
                        <tr>
                            <td class="fw-semibold">{{ $template->name }}</td>
                            <td class="text-muted">{{ $template->description ?? '—' }}</td>
                            <td>{{ $template->compression_type ?? 'None' }}</td>
                            <td>{{ $template->encryption_type ?? 'None' }}</td>
                            <td>{{ $template->retention_days }} days</td>
                            <td>
                                @if($template->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-secondary">Inactive</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('backup-templates.edit', $template) }}" class="btn btn-sm btn-primary" title="Edit"><i class="bi bi-pencil"></i></a>
                                <form action="{{ route('backup-templates.destroy', $template) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this template?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" title="Delete"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="text-center py-5">
                <i class="bi bi-collection display-3 text-muted"></i>
                <h4 class="mt-3">No Templates Found</h4>
                <p class="text-muted">Create a template to get started.</p>
                <a href="{{ route('backup-templates.create') }}" class="btn btn-primary">Create Template</a>
            </div>
            @endif
        </div>
    </div>
@endsection
