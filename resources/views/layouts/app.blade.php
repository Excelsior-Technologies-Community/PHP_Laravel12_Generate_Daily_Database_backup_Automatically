<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Database Backup Manager')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    @stack('styles')
    <style>
        body { background: #f4f7fb; min-height: 100vh; }
        .navbar-brand { font-weight: 700; }
        .hero { background: linear-gradient(135deg, #0d6efd, #084298); color: white; border-radius: 18px; padding: 30px; margin-top: 30px; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.10); }
        .stat-card { border: none; border-radius: 16px; background: white; padding: 24px; height: 100%; box-shadow: 0 5px 20px rgba(0, 0, 0, 0.06); }
        .stat-icon { width: 52px; height: 52px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 23px; background: #e9f2ff; color: #0d6efd; margin-bottom: 15px; }
        .stat-number { font-size: 28px; font-weight: 700; }
        .backup-card { border: none; border-radius: 16px; background: white; box-shadow: 0 5px 20px rgba(0, 0, 0, 0.06); overflow: hidden; }
        .table> :not(caption)>*>* { padding: 15px; vertical-align: middle; }
        .file-icon { width: 42px; height: 42px; border-radius: 10px; background: #e8f5e9; color: #198754; display: flex; align-items: center; justify-content: center; font-size: 20px; }
        .filter-card { background: white; border-radius: 16px; padding: 20px; box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05); }
        .footer { color: #6c757d; padding: 30px 0; }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark bg-dark shadow-sm">
        <div class="container">
            <a href="{{ route('backups.index') }}" class="navbar-brand text-decoration-none">
                <i class="bi bi-database-check me-2"></i> Database Backup Manager
            </a>
            <div class="d-flex gap-2">
                <a href="{{ route('backups.index') }}" class="btn btn-outline-light btn-sm {{ request()->routeIs('backups.*') ? 'active' : '' }}">
                    <i class="bi bi-archive me-1"></i> Backups
                </a>
                <a href="{{ route('backup-health.index') }}" class="btn btn-outline-light btn-sm {{ request()->routeIs('backup-health.*') ? 'active' : '' }}">
                    <i class="bi bi-heart-pulse me-1"></i> Health
                </a>
                <a href="{{ route('backup-templates.index') }}" class="btn btn-outline-light btn-sm {{ request()->routeIs('backup-templates.*') ? 'active' : '' }}">
                    <i class="bi bi-collection me-1"></i> Templates
                </a>
                <a href="{{ route('backup-schedules.index') }}" class="btn btn-outline-light btn-sm {{ request()->routeIs('backup-schedules.*') ? 'active' : '' }}">
                    <i class="bi bi-calendar3 me-1"></i> Schedules
                </a>
                <a href="{{ route('backup-comparisons.index') }}" class="btn btn-outline-light btn-sm {{ request()->routeIs('backup-comparisons.*') ? 'active' : '' }}">
                    <i class="bi bi-arrow-left-right me-1"></i> Compare
                </a>
                <a href="{{ route('backup-settings.index') }}" class="btn btn-outline-light btn-sm {{ request()->routeIs('backup-settings.*') ? 'active' : '' }}">
                    <i class="bi bi-gear me-1"></i> Settings
                </a>
            </div>
        </div>
    </nav>

    <div class="container pb-5">
        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mt-4">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show mt-4">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        @yield('content')

        <div class="text-center footer">Laravel 12 Database Backup Management System</div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
