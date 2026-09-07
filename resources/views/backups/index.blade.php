<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Database Backup Manager</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css"
        rel="stylesheet"
    >

    <style>
        body {
            background: #f4f7fb;
            min-height: 100vh;
        }

        .navbar-brand {
            font-weight: 700;
        }

        .hero {
            background: linear-gradient(
                135deg,
                #0d6efd,
                #084298
            );
            color: white;
            border-radius: 18px;
            padding: 30px;
            margin-top: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.10);
        }

        .stat-card {
            border: none;
            border-radius: 16px;
            background: white;
            padding: 24px;
            height: 100%;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.06);
            transition: 0.2s;
        }

        .stat-card:hover {
            transform: translateY(-3px);
        }

        .stat-icon {
            width: 52px;
            height: 52px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 23px;
            background: #e9f2ff;
            color: #0d6efd;
            margin-bottom: 15px;
        }

        .stat-number {
            font-size: 28px;
            font-weight: 700;
        }

        .backup-card {
            border: none;
            border-radius: 16px;
            background: white;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.06);
            overflow: hidden;
        }

        .table > :not(caption) > * > * {
            padding: 16px;
            vertical-align: middle;
        }

        .file-icon {
            width: 42px;
            height: 42px;
            border-radius: 10px;
            background: #e8f5e9;
            color: #198754;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }

        .badge-retention {
            background: #fff3cd;
            color: #856404;
        }

        .empty-state {
            padding: 70px 20px;
            text-align: center;
        }

        .empty-state i {
            font-size: 60px;
            color: #adb5bd;
        }

        .footer {
            color: #6c757d;
            padding: 30px 0;
        }
    </style>
</head>

<body>

<nav class="navbar navbar-dark bg-dark shadow-sm">
    <div class="container">
        <span class="navbar-brand">
            <i class="bi bi-database-check me-2"></i>
            Database Backup Manager
        </span>

        <span class="text-white-50 small">
            Laravel 12
        </span>
    </div>
</nav>

<div class="container pb-5">

    {{-- Success Message --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mt-4">
            <i class="bi bi-check-circle-fill me-2"></i>
            {{ session('success') }}

            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert"
            ></button>
        </div>
    @endif

    {{-- Error Message --}}
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show mt-4">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            {{ session('error') }}

            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert"
            ></button>
        </div>
    @endif

    {{-- Hero --}}
    <div class="hero">

        <div class="row align-items-center">

            <div class="col-md-8">

                <h1 class="fw-bold mb-3">
                    Database Backup Dashboard
                </h1>

                <p class="mb-0 fs-5">
                    Manage, monitor and protect your MySQL database
                    backups from one place.
                </p>

            </div>

            <div class="col-md-4 text-md-end mt-4 mt-md-0">

                <form
                    action="{{ route('backups.create') }}"
                    method="POST"
                >
                    @csrf

                    <button
                        type="submit"
                        class="btn btn-light btn-lg fw-semibold"
                        onclick="return confirm('Create a new database backup now?')"
                    >
                        <i class="bi bi-cloud-arrow-down me-2"></i>
                        Create Backup Now
                    </button>
                </form>

            </div>

        </div>

    </div>

    {{-- Statistics --}}
    <div class="row g-4 mt-2">

        <div class="col-md-4">

            <div class="stat-card">

                <div class="stat-icon">
                    <i class="bi bi-database"></i>
                </div>

                <div class="text-muted">
                    Total Backups
                </div>

                <div class="stat-number">
                    {{ $totalBackups }}
                </div>

            </div>

        </div>

        <div class="col-md-4">

            <div class="stat-card">

                <div class="stat-icon">
                    <i class="bi bi-hdd"></i>
                </div>

                <div class="text-muted">
                    Total Storage Used
                </div>

                <div class="stat-number">
                    {{ $totalSize }}
                </div>

            </div>

        </div>

        <div class="col-md-4">

            <div class="stat-card">

                <div class="stat-icon">
                    <i class="bi bi-clock-history"></i>
                </div>

                <div class="text-muted">
                    Backup Retention
                </div>

                <div class="stat-number">
                    {{ $retentionDays }}
                    <span class="fs-6 fw-normal">days</span>
                </div>

            </div>

        </div>

    </div>

    {{-- Backup Table --}}
    <div class="backup-card mt-5">

        <div class="p-4 border-bottom">

            <div class="d-flex justify-content-between align-items-center">

                <div>
                    <h4 class="fw-bold mb-1">
                        <i class="bi bi-archive me-2"></i>
                        Backup Files
                    </h4>

                    <p class="text-muted mb-0">
                        All available MySQL database backups
                    </p>
                </div>

                <span class="badge bg-primary fs-6">
                    {{ $totalBackups }} Files
                </span>

            </div>

        </div>

        @if($backups->count() > 0)

            <div class="table-responsive">

                <table class="table table-hover mb-0">

                    <thead class="table-light">

                        <tr>

                            <th>Backup File</th>

                            <th>Created Date</th>

                            <th>Time</th>

                            <th>Size</th>

                            <th>Age</th>

                            <th class="text-end">
                                Actions
                            </th>

                        </tr>

                    </thead>

                    <tbody>

                        @foreach($backups as $backup)

                            <tr>

                                <td>

                                    <div class="d-flex align-items-center gap-3">

                                        <div class="file-icon">
                                            <i class="bi bi-filetype-sql"></i>
                                        </div>

                                        <div>

                                            <div class="fw-semibold">
                                                {{ $backup['name'] }}
                                            </div>

                                            <small class="text-muted">
                                                MySQL SQL Backup
                                            </small>

                                        </div>

                                    </div>

                                </td>

                                <td>
                                    {{ $backup['created_date'] }}
                                </td>

                                <td>
                                    {{ $backup['created_time'] }}
                                </td>

                                <td>
                                    <span class="badge bg-light text-dark">
                                        {{ $backup['size'] }}
                                    </span>
                                </td>

                                <td>

                                    @if($backup['age_days'] === 0)

                                        <span class="badge bg-success">
                                            Today
                                        </span>

                                    @elseif($backup['age_days'] === 1)

                                        <span class="badge bg-info">
                                            Yesterday
                                        </span>

                                    @else

                                        <span class="badge badge-retention">
                                            {{ $backup['age_days'] }} days old
                                        </span>

                                    @endif

                                </td>

                                <td>

                                    <div class="d-flex justify-content-end gap-2">

                                        {{-- Download --}}
                                        <a
                                            href="{{ route('backups.download', $backup['name']) }}"
                                            class="btn btn-sm btn-primary"
                                            title="Download Backup"
                                        >
                                            <i class="bi bi-download"></i>
                                        </a>

                                        {{-- Delete --}}
                                        <form
                                            action="{{ route('backups.destroy', $backup['name']) }}"
                                            method="POST"
                                            onsubmit="return confirm('Are you sure you want to delete this backup? This action cannot be undone.')"
                                        >

                                            @csrf

                                            @method('DELETE')

                                            <button
                                                type="submit"
                                                class="btn btn-sm btn-danger"
                                                title="Delete Backup"
                                            >
                                                <i class="bi bi-trash"></i>
                                            </button>

                                        </form>

                                    </div>

                                </td>

                            </tr>

                        @endforeach

                    </tbody>

                </table>

            </div>

        @else

            <div class="empty-state">

                <i class="bi bi-database-x"></i>

                <h4 class="mt-4">
                    No Backups Found
                </h4>

                <p class="text-muted">
                    Create your first database backup to see it here.
                </p>

                <form
                    action="{{ route('backups.create') }}"
                    method="POST"
                    class="mt-3"
                >

                    @csrf

                    <button
                        type="submit"
                        class="btn btn-primary"
                    >
                        <i class="bi bi-plus-circle me-2"></i>
                        Create First Backup
                    </button>

                </form>

            </div>

        @endif

    </div>

    {{-- Information --}}
    <div class="alert alert-info mt-4">

        <div class="d-flex">

            <i class="bi bi-info-circle-fill fs-4 me-3"></i>

            <div>

                <strong>Automatic Backup Retention</strong>

                <p class="mb-0 mt-1">
                    Backups older than
                    <strong>{{ $retentionDays }} days</strong>
                    are automatically removed when the scheduled
                    backup task runs.
                </p>

            </div>

        </div>

    </div>

    <div class="footer text-center">
        Laravel 12 Database Backup Management System
    </div>

</div>

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>

</body>
</html>