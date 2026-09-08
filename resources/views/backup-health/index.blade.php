@extends('layouts.app')

@section('title', 'Backup Health Monitoring')

@section('content')
    {{-- Hero --}}
    <div class="hero mb-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h1 class="fw-bold mb-2">Backup Health Monitoring</h1>
                <p class="mb-0 text-white-50">Verify backup integrity, monitor backup status, and detect overdue or modified backups.</p>
            </div>
            <div class="text-end">
                @if($healthStatus === 'healthy')
                    <span class="badge bg-success fs-6 px-3 py-2"><i class="bi bi-check-circle me-1"></i> Healthy</span>
                @elseif($healthStatus === 'warning')
                    <span class="badge bg-warning text-dark fs-6 px-3 py-2"><i class="bi bi-exclamation-triangle me-1"></i> Warning</span>
                @else
                    <span class="badge bg-danger fs-6 px-3 py-2"><i class="bi bi-x-circle me-1"></i> Critical</span>
                @endif
            </div>
        </div>
    </div>

    {{-- Health Message --}}
    <div class="alert alert-{{ $healthClass }} shadow-sm">
        <div class="d-flex align-items-start">
            <i class="bi
                @if($healthStatus === 'healthy') bi-check-circle
                @elseif($healthStatus === 'warning') bi-exclamation-triangle
                @else bi-x-circle
                @endif fs-4 me-3"></i>
            <div>
                <strong>Backup Health:</strong> {{ $healthMessage }}
                @if($ageHours !== null)
                    <div class="small mt-1">Latest successful backup: {{ $ageHours }} hour(s) ago.</div>
                @endif
            </div>
        </div>
    </div>

    {{-- Statistics --}}
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card health-card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="text-muted small">Successful Backups</div>
                            <div class="stat-number text-success">{{ $successfulBackups }}</div>
                        </div>
                        <div class="health-icon bg-success-subtle text-success"><i class="bi bi-check-circle"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card health-card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="text-muted small">Failed Backups</div>
                            <div class="stat-number text-danger">{{ $failedBackups }}</div>
                        </div>
                        <div class="health-icon bg-danger-subtle text-danger"><i class="bi bi-x-circle"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card health-card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="text-muted small">Latest Backup</div>
                            <div class="fw-bold mt-2">
                                @if($latestSuccessfulBackup)
                                    {{ $latestSuccessfulBackup->created_at->format('d M Y') }}
                                @else
                                    Never
                                @endif
                            </div>
                        </div>
                        <div class="health-icon bg-primary-subtle text-primary"><i class="bi bi-clock-history"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card health-card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="text-muted small">Total Backup Records</div>
                            <div class="stat-number">{{ $totalBackups }}</div>
                        </div>
                        <div class="health-icon bg-info-subtle text-info"><i class="bi bi-bar-chart"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Integrity Verification --}}
    <div class="card table-card mb-4">
        <div class="card-header bg-white py-3">
            <h5 class="section-title mb-1"><i class="bi bi-shield-check me-2"></i> Backup Integrity Verification</h5>
            <div class="text-muted small">SHA-256 checksums are compared with the checksum recorded when each backup was created.</div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Backup File</th>
                        <th>Size</th>
                        <th>Created</th>
                        <th>Integrity</th>
                        <th>SHA-256 Checksum</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($backupFiles as $backup)
                    <tr>
                        <td><div class="fw-semibold"><i class="bi bi-file-earmark-code me-2"></i> {{ $backup['filename'] }}</div></td>
                        <td>{{ $backup['size'] }}</td>
                        <td>{{ $backup['created_at']->format('d M Y') }}<div class="small text-muted">{{ $backup['created_at']->format('h:i A') }}</div></td>
                        <td>
                            @if($backup['status'] === 'healthy')
                                <span class="badge bg-success status-badge"><i class="bi bi-check-circle me-1"></i> Healthy</span>
                            @elseif($backup['status'] === 'warning')
                                <span class="badge bg-warning text-dark status-badge"><i class="bi bi-exclamation-triangle me-1"></i> Modified</span>
                            @else
                                <span class="badge bg-danger status-badge"><i class="bi bi-x-circle me-1"></i> Invalid</span>
                            @endif
                        </td>
                        <td><div class="checksum">{{ $backup['checksum'] }}</div></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-5">
                            <i class="bi bi-database-x display-5 text-muted"></i>
                            <h5 class="mt-3">No backup files found</h5>
                            <p class="text-muted mb-0">Create your first database backup to start integrity monitoring.</p>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Recent Logs --}}
    <div class="card table-card">
        <div class="card-header bg-white py-3">
            <h5 class="section-title mb-0"><i class="bi bi-journal-text me-2"></i> Recent Backup Activity</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Time</th>
                        <th>File</th>
                        <th>Status</th>
                        <th>Size</th>
                        <th>Message</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($recentLogs as $log)
                    <tr>
                        <td>{{ $log->created_at->format('d M Y') }}<div class="small text-muted">{{ $log->created_at->format('h:i A') }}</div></td>
                        <td>{{ $log->filename ?? 'N/A' }}</td>
                        <td>
                            @if($log->status === 'success')
                                <span class="badge bg-success status-badge"><i class="bi bi-check-circle me-1"></i> Success</span>
                            @else
                                <span class="badge bg-danger status-badge"><i class="bi bi-x-circle me-1"></i> Failed</span>
                            @endif
                        </td>
                        <td>{{ number_format($log->size_bytes / 1024, 2) }} KB</td>
                        <td><span class="small">{{ $log->message }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center py-5 text-muted">No backup activity has been recorded yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
