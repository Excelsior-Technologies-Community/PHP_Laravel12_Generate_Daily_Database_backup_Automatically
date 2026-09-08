@extends('layouts.app')

@section('title', 'Database Backup Manager')

@section('content')
    {{-- Hero --}}
    <div class="hero">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="fw-bold mb-3">Database Backup Dashboard</h1>
                <p class="mb-0 fs-5">Manage, monitor and protect your MySQL database backups.</p>
            </div>
            <div class="col-md-4 text-md-end mt-4 mt-md-0">
                <form action="{{ route('backups.create') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-light btn-lg fw-semibold" onclick="return confirm('Create a new database backup now?')">
                        <i class="bi bi-cloud-arrow-down me-2"></i> Create Backup
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- Statistics --}}
    <div class="row g-4 mt-2">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon"><i class="bi bi-database"></i></div>
                <div class="text-muted">Total Backups</div>
                <div class="stat-number">{{ $totalBackups }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon"><i class="bi bi-hdd"></i></div>
                <div class="text-muted">Total Storage</div>
                <div class="stat-number">{{ $totalSize }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon"><i class="bi bi-check-circle"></i></div>
                <div class="text-muted">Successful Logs</div>
                <div class="stat-number text-success">{{ $successfulLogs }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon"><i class="bi bi-x-circle"></i></div>
                <div class="text-muted">Failed Logs</div>
                <div class="stat-number text-danger">{{ $failedLogs }}</div>
            </div>
        </div>
    </div>

    {{-- Latest / Oldest --}}
    <div class="row g-4 mt-2">
        <div class="col-md-6">
            <div class="alert alert-success">
                <strong><i class="bi bi-arrow-up-circle me-1"></i> Latest Backup:</strong>
                @if($latestBackup) {{ $latestBackup->getFilename() }} @else None @endif
            </div>
        </div>
        <div class="col-md-6">
            <div class="alert alert-secondary">
                <strong><i class="bi bi-arrow-down-circle me-1"></i> Oldest Backup:</strong>
                @if($oldestBackup) {{ $oldestBackup->getFilename() }} @else None @endif
            </div>
        </div>
    </div>

    {{-- Filter Area --}}
    <div class="filter-card mt-4">
        <form method="GET" action="{{ route('backups.index') }}">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label fw-semibold"><i class="bi bi-search"></i> Search Backup</label>
                    <input type="text" name="search" value="{{ $search }}" class="form-control" placeholder="backup-2026">
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Sort</label>
                    <select name="sort" class="form-select">
                        <option value="newest" @selected($sort==='newest')>Newest</option>
                        <option value="oldest" @selected($sort==='oldest')>Oldest</option>
                        <option value="largest" @selected($sort==='largest')>Largest</option>
                        <option value="smallest" @selected($sort==='smallest')>Smallest</option>
                        <option value="name_asc" @selected($sort==='name_asc')>Name A-Z</option>
                        <option value="name_desc" @selected($sort==='name_desc')>Name Z-A</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">From Date</label>
                    <input type="date" name="date_from" value="{{ $dateFrom }}" class="form-control">
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">To Date</label>
                    <input type="date" name="date_to" value="{{ $dateTo }}" class="form-control">
                </div>
                <div class="col-md-3">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-funnel me-1"></i> Filter</button>
                        <a href="{{ route('backups.index') }}" class="btn btn-outline-secondary">Reset</a>
                    </div>
                </div>
            </div>
        </form>
    </div>

    {{-- Management Buttons --}}
    <div class="d-flex justify-content-between align-items-center mt-4 flex-wrap gap-2">
        <div>
            <span class="badge bg-primary">{{ $backups->total() }} matching files</span>
            <span class="badge bg-warning text-dark">Retention: {{ $retentionDays }} days</span>
        </div>
        <div class="d-flex gap-2">
            <form action="{{ route('backups.cleanup') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-warning" onclick="return confirm('Delete backups older than {{ $retentionDays }} days?')">
                    <i class="bi bi-trash3 me-1"></i> Cleanup Old
                </button>
            </form>
            <a href="{{ route('backups.export-logs') }}" class="btn btn-success">
                <i class="bi bi-file-earmark-spreadsheet me-1"></i> Export Logs
            </a>
        </div>
    </div>

    {{-- Backup Table --}}
    <div class="backup-card mt-4">
        <form action="{{ route('backups.bulk-delete') }}" method="POST" id="bulkDeleteForm">
            @csrf @method('DELETE')
            <div class="p-4 border-bottom">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="fw-bold mb-1"><i class="bi bi-archive me-2"></i> Backup Files</h4>
                        <p class="text-muted mb-0">Search, filter, verify, download, restore or delete backups.</p>
                    </div>
                    <button type="submit" class="btn btn-danger" onclick="return confirmBulkDelete()">
                        <i class="bi bi-trash me-1"></i> Delete Selected
                    </button>
                </div>
            </div>

            @if($backups->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th><input type="checkbox" class="form-check-input" id="selectAll"></th>
                            <th>Backup File</th>
                            <th>Created</th>
                            <th>Size</th>
                            <th>Age</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($backups as $backup)
                        <tr>
                            <td><input type="checkbox" name="filenames[]" value="{{ $backup['name'] }}" class="form-check-input backup-checkbox"></td>
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="file-icon"><i class="bi bi-filetype-sql"></i></div>
                                    <div>
                                        <div class="fw-semibold">{{ $backup['name'] }}</div>
                                        <small class="text-muted">MySQL SQL Backup</small>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $backup['created_date'] }}<div class="small text-muted">{{ $backup['created_time'] }}</div></td>
                            <td><span class="badge bg-light text-dark">{{ $backup['size'] }}</span></td>
                            <td>
                                @if($backup['age_days'] === 0)
                                    <span class="badge bg-success">Today</span>
                                @elseif($backup['age_days'] === 1)
                                    <span class="badge bg-info">Yesterday</span>
                                @else
                                    <span class="badge bg-warning text-dark">{{ $backup['age_days'] }} days old</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex justify-content-end gap-1">
                                    <a href="{{ route('backups.verify', $backup['name']) }}" class="btn btn-sm btn-success" title="Verify Integrity"><i class="bi bi-shield-check"></i></a>
                                    <a href="{{ route('backups.download', $backup['name']) }}" class="btn btn-sm btn-primary" title="Download"><i class="bi bi-download"></i></a>
                                    <form action="{{ route('backups.restore', $backup['name']) }}" method="POST" class="d-inline" onsubmit="return confirm('Restore database from {{ addslashes($backup['name']) }}? This will overwrite the current database.')">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-warning" title="Restore"><i class="bi bi-arrow-counterclockwise"></i></button>
                                    </form>
                                    <form action="{{ route('backups.upload', $backup['name']) }}" method="POST" class="d-inline" onsubmit="return confirm('Upload {{ addslashes($backup['name']) }} to cloud?')">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-info" title="Upload to Cloud"><i class="bi bi-cloud-arrow-up"></i></button>
                                    </form>
                                    <button type="button" class="btn btn-sm btn-danger" title="Delete" onclick="deleteBackup('{{ addslashes($backup['name']) }}')"><i class="bi bi-trash"></i></button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-4">
                <div class="d-flex justify-content-center">
                    @if ($backups->lastPage() > 1)
                    <nav>
                        <ul class="pagination mb-0">
                            @for ($page = 1; $page <= $backups->lastPage(); $page++)
                            <li class="page-item {{ $page == $backups->currentPage() ? 'active' : '' }}">
                                <a class="page-link" href="{{ $backups->url($page) }}">{{ $page }}</a>
                            </li>
                            @endfor
                        </ul>
                    </nav>
                    @endif
                </div>
            </div>
            @else
            <div class="text-center py-5">
                <i class="bi bi-database-x display-3 text-muted"></i>
                <h4 class="mt-3">No Backups Found</h4>
                <p class="text-muted">Try changing your search or filters.</p>
            </div>
            @endif
        </form>
    </div>

    {{-- Hidden Delete Form --}}
    <form id="singleDeleteForm" method="POST" style="display:none;">
        @csrf @method('DELETE')
    </form>

    {{-- Retention Information --}}
    <div class="alert alert-info mt-4">
        <div class="d-flex">
            <i class="bi bi-info-circle-fill fs-4 me-3"></i>
            <div>
                <strong>Backup Retention</strong>
                <p class="mb-0 mt-1">Backups older than <strong>{{ $retentionDays }} days</strong> can be removed using the <strong>Cleanup Old</strong> button.</p>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.getElementById('selectAll')?.addEventListener('change', function() {
        document.querySelectorAll('.backup-checkbox').forEach(function(checkbox) {
            checkbox.checked = document.getElementById('selectAll').checked;
        });
    });

    function confirmBulkDelete() {
        const selected = document.querySelectorAll('.backup-checkbox:checked');
        if (selected.length === 0) {
            alert('Please select at least one backup.');
            return false;
        }
        return confirm('Are you sure you want to delete ' + selected.length + ' selected backup(s)?');
    }

    function deleteBackup(filename) {
        if (!confirm('Are you sure you want to delete ' + filename + '?')) {
            return;
        }
        const form = document.getElementById('singleDeleteForm');
        form.action = "{{ url('/backups') }}/" + encodeURIComponent(filename);
        form.submit();
    }
</script>
@endpush
