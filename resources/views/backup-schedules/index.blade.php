@extends('layouts.app')

@section('title', 'Backup Schedules')

@section('content')
    <div class="hero mt-4">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="fw-bold mb-3">Backup Schedules</h1>
                <p class="mb-0 fs-5">Configure automated backup schedules with custom cron expressions.</p>
            </div>
            <div class="col-md-4 text-md-end mt-4 mt-md-0">
                <a href="{{ route('backup-schedules.create') }}" class="btn btn-light btn-lg fw-semibold"><i class="bi bi-plus-circle me-2"></i> New Schedule</a>
            </div>
        </div>
    </div>

    <div class="backup-card mt-4">
        <div class="p-4">
            @if($schedules->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>Cron</th>
                            <th>Timezone</th>
                            <th>Retention</th>
                            <th>Auto Cleanup</th>
                            <th>Auto Verify</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($schedules as $schedule)
                        <tr>
                            <td class="fw-semibold">{{ $schedule->name }}</td>
                            <td><code>{{ $schedule->cron_expression }}</code></td>
                            <td>{{ $schedule->timezone }}</td>
                            <td>{{ $schedule->retention_days }} days</td>
                            <td>{{ $schedule->auto_cleanup ? 'Yes' : 'No' }}</td>
                            <td>{{ $schedule->auto_verify ? 'Yes' : 'No' }}</td>
                            <td>
                                @if($schedule->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-secondary">Inactive</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('backup-schedules.edit', $schedule) }}" class="btn btn-sm btn-primary" title="Edit"><i class="bi bi-pencil"></i></a>
                                <form action="{{ route('backup-schedules.destroy', $schedule) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this schedule?')">
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
                <i class="bi bi-calendar3 display-3 text-muted"></i>
                <h4 class="mt-3">No Schedules Found</h4>
                <p class="text-muted">Create a schedule to automate backups.</p>
                <a href="{{ route('backup-schedules.create') }}" class="btn btn-primary">Create Schedule</a>
            </div>
            @endif
        </div>
    </div>
@endsection
