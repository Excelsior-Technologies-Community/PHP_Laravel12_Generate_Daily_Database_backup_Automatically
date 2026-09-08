@extends('layouts.app')

@section('title', 'Backup Settings')

@section('content')
    <div class="hero mt-4">
        <h1 class="fw-bold mb-0">Backup Settings</h1>
    </div>

    <div class="backup-card mt-4">
        <div class="p-4">
            <h5 class="fw-bold mb-3">Global Settings</h5>
            <form method="POST" action="{{ route('backup-settings.update') }}">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Retention Days</label>
                        <input type="number" name="settings[BACKUP_RETENTION_DAYS]" class="form-control" value="{{ config('backup.retention.days', 7) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Default Cron</label>
                        <input type="text" name="settings[BACKUP_SCHEDULE_DEFAULT_CRON]" class="form-control" value="{{ config('backup.schedule.default_cron', '0 0 * * *') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Timezone</label>
                        <input type="text" name="settings[BACKUP_SCHEDULE_TIMEZONE]" class="form-control" value="{{ config('backup.schedule.timezone', 'UTC') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Checksum Algorithm</label>
                        <select name="settings[BACKUP_CHECKSUM_ALGORITHM]" class="form-select">
                            <option value="sha256" @selected(config('backup.verification.checksum_algorithm') === 'sha256')>SHA-256</option>
                            <option value="md5" @selected(config('backup.verification.checksum_algorithm') === 'md5')>MD5</option>
                        </select>
                    </div>
                </div>
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle me-1"></i> Save Settings</button>
                </div>
            </form>
        </div>
    </div>

    <div class="backup-card mt-4">
        <div class="p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0">Notifications</h5>
                <button class="btn btn-sm btn-primary" type="button" data-bs-toggle="collapse" data-bs-target="#addNotification">
                    <i class="bi bi-plus me-1"></i> Add Notification
                </button>
            </div>
            <div class="collapse mb-3" id="addNotification">
                <form method="POST" action="{{ route('backup-settings.notifications.store') }}" class="row g-3">
                    @csrf
                    <div class="col-md-4">
                        <label class="form-label">Channel</label>
                        <select name="channel" class="form-select" required>
                            <option value="email">Email</option>
                            <option value="slack">Slack</option>
                            <option value="discord">Discord</option>
                            <option value="telegram">Telegram</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" class="form-control" placeholder="Optional">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Events (comma separated)</label>
                        <input type="text" name="events[]" class="form-control" value="backup.created,backup.failed" placeholder="backup.created,backup.failed">
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-success"><i class="bi bi-plus-circle me-1"></i> Add Notification</button>
                    </div>
                </form>
            </div>
            @if($notifications->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Channel</th>
                            <th>Name</th>
                            <th>Events</th>
                            <th>Active</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($notifications as $notification)
                        <tr>
                            <td class="fw-semibold">{{ ucfirst($notification->channel) }}</td>
                            <td>{{ $notification->name ?? '—' }}</td>
                            <td>{{ implode(', ', $notification->events ?? []) }}</td>
                            <td>
                                @if($notification->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-secondary">Inactive</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <form action="{{ route('backup-settings.notifications.destroy', $notification) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this notification?')">
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
                <i class="bi bi-bell display-3 text-muted"></i>
                <h4 class="mt-3">No Notifications</h4>
                <p class="text-muted">Add notification channels using the button above.</p>
            </div>
            @endif
        </div>
    </div>

    <div class="backup-card mt-4">
        <div class="p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0">Cloud Configurations</h5>
                <button class="btn btn-sm btn-primary" type="button" data-bs-toggle="collapse" data-bs-target="#addCloudConfig">
                    <i class="bi bi-plus me-1"></i> Add Cloud Config
                </button>
            </div>
            <div class="collapse mb-3" id="addCloudConfig">
                <form method="POST" action="{{ route('backup-settings.cloud-configs.store') }}" class="row g-3">
                    @csrf
                    <div class="col-md-4">
                        <label class="form-label">Provider</label>
                        <select name="provider" class="form-select" required>
                            <option value="s3">AWS S3</option>
                            <option value="google_drive">Google Drive</option>
                            <option value="dropbox">Dropbox</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" class="form-control" placeholder="Optional">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Bucket / Folder</label>
                        <input type="text" name="bucket" class="form-control" placeholder="bucket-name or folder-id">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Region</label>
                        <input type="text" name="region" class="form-control" placeholder="us-east-1">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Path</label>
                        <input type="text" name="path" class="form-control" placeholder="backups">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Endpoint (optional)</label>
                        <input type="text" name="endpoint" class="form-control" placeholder="https://...">
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-success"><i class="bi bi-plus-circle me-1"></i> Add Cloud Config</button>
                    </div>
                </form>
            </div>
            @if($cloudConfigs->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Provider</th>
                            <th>Name</th>
                            <th>Bucket/Path</th>
                            <th>Active</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($cloudConfigs as $config)
                        <tr>
                            <td class="fw-semibold">{{ ucfirst($config->provider) }}</td>
                            <td>{{ $config->name ?? '—' }}</td>
                            <td>{{ $config->bucket ?? $config->path ?? '—' }}</td>
                            <td>
                                @if($config->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-secondary">Inactive</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <form action="{{ route('backup-settings.cloud-configs.destroy', $config) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this cloud config?')">
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
                <i class="bi bi-cloud display-3 text-muted"></i>
                <h4 class="mt-3">No Cloud Configurations</h4>
                <p class="text-muted">Add cloud storage configurations to enable auto-upload.</p>
            </div>
            @endif
        </div>
    </div>
@endsection
