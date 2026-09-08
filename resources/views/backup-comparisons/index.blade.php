@extends('layouts.app')

@section('title', 'Backup Comparisons')

@section('content')
    <div class="hero mt-4">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="fw-bold mb-3">Backup Comparisons</h1>
                <p class="mb-0 fs-5">Compare two backups to identify differences.</p>
            </div>
        </div>
    </div>

    <div class="backup-card mt-4">
        <div class="p-4">
            <form method="POST" action="{{ route('backup-comparisons.compare') }}" class="row g-3 align-items-end">
                @csrf
                <div class="col-md-5">
                    <label class="form-label fw-semibold">Backup A</label>
                    <select name="file_a" class="form-select" required>
                        <option value="">Select backup</option>
                        @php
                        $backupFiles = collect(scandir(storage_path('app/backup')))
                            ->filter(fn($f) => !in_array($f, ['.', '..']) && (strtolower(pathinfo($f, PATHINFO_EXTENSION)) === 'sql' || str_ends_with(strtolower($f), '.sql.gz')))
                            ->sortDesc();
                        @endphp
                        @foreach($backupFiles as $file)
                        <option value="{{ $file }}">{{ $file }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-5">
                    <label class="form-label fw-semibold">Backup B</label>
                    <select name="file_b" class="form-select" required>
                        <option value="">Select backup</option>
                        @foreach($backupFiles as $file)
                        <option value="{{ $file }}">{{ $file }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-arrow-left-right me-1"></i> Compare</button>
                </div>
            </form>
        </div>
    </div>

    @if(session('comparison_result'))
    @php $comparison = session('comparison_result'); @endphp
    <div class="backup-card mt-4">
        <div class="p-4">
            <h5 class="fw-bold mb-3">Comparison Result</h5>
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="alert alert-info">
                        <strong>Backup A:</strong> {{ $comparison->result['file_a']['filename'] }}<br>
                        Size: {{ number_format($comparison->result['file_a']['size_bytes']) }} bytes<br>
                        Tables: {{ $comparison->result['file_a']['tables_count'] }}<br>
                        Lines: {{ $comparison->result['file_a']['lines_count'] }}
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="alert alert-info">
                        <strong>Backup B:</strong> {{ $comparison->result['file_b']['filename'] }}<br>
                        Size: {{ number_format($comparison->result['file_b']['size_bytes']) }} bytes<br>
                        Tables: {{ $comparison->result['file_b']['tables_count'] }}<br>
                        Lines: {{ $comparison->result['file_b']['lines_count'] }}
                    </div>
                </div>
            </div>
            <div class="mt-3">
                <strong>Diff:</strong>
                <ul>
                    <li>Size difference: {{ number_format($comparison->result['diff']['size_diff_bytes']) }} bytes</li>
                    <li>Lines difference: {{ $comparison->result['diff']['lines_diff'] }}</li>
                    <li>Tables added: {{ count($comparison->result['diff']['tables_added']) }}</li>
                    <li>Tables removed: {{ count($comparison->result['diff']['tables_removed']) }}</li>
                </ul>
            </div>
        </div>
    </div>
    @endif

    <div class="backup-card mt-4">
        <div class="p-4">
            <h5 class="fw-bold mb-3">Comparison History</h5>
            @if($comparisons->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Backup A</th>
                            <th>Backup B</th>
                            <th>Compared At</th>
                            <th>Identical</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($comparisons as $comparison)
                        <tr>
                            <td>{{ $comparison->backup_a_filename }}</td>
                            <td>{{ $comparison->backup_b_filename }}</td>
                            <td>{{ $comparison->compared_at }}</td>
                            <td>
                                @if($comparison->result['are_identical'] ?? false)
                                    <span class="badge bg-success">Yes</span>
                                @else
                                    <span class="badge bg-danger">No</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="text-center py-5">
                <i class="bi bi-arrow-left-right display-3 text-muted"></i>
                <h4 class="mt-3">No Comparisons Yet</h4>
                <p class="text-muted">Compare two backups above to get started.</p>
            </div>
            @endif
        </div>
    </div>
@endsection
