@extends('layouts.app')

@section('title', 'Database Backup')

@section('sidebar')
    <a href="{{ route('admin.dashboard') }}" class="sidebar-item">Dashboard</a>
    <a href="{{ route('admin.users') }}" class="sidebar-item">User Management</a>
    <a href="{{ route('admin.pets') }}" class="sidebar-item">Pet Management</a>
    <a href="{{ route('admin.analytics') }}" class="sidebar-item">Analytics</a>
    <a href="{{ route('admin.backup') }}" class="sidebar-item active">Database Backup</a>
@endsection

@section('content')
    <section class="hero-card">
        <div class="hero-row">
            <div>
                <h1 class="hero-title">Database Backup</h1>
                <p class="hero-copy">Download a SQL backup of the current database.</p>
            </div>
        </div>
    </section>

    @if ($errors->any())
        <div class="alert banner-danger" role="alert">
            <strong>The backup could not be prepared.</strong>
            <ul style="margin: 0.5rem 0 0; padding-left: 1.25rem;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <section class="card" style="max-width: 760px;">
        <div class="surface-header">
            <div>
                <h2>Download Current Database</h2>
                <p class="section-copy">The selected date is used in the backup filename only. The file contains the database configured for this app when you download it and streams directly without using the server's temporary-file directory.</p>
            </div>
        </div>

        <form action="{{ route('admin.backup.download') }}" method="POST" class="stack">
            @csrf
            <label class="field" for="backup_date">
                <span class="field-label">Date in backup filename</span>
                <input
                    id="backup_date"
                    name="backup_date"
                    type="date"
                    class="field-control"
                    value="{{ old('backup_date', now()->toDateString()) }}"
                    max="{{ now()->toDateString() }}"
                    required
                >
            </label>

            <p class="muted-copy">The backup is downloaded as a <code>.sql</code> file. Keep it in a secure location because it contains database information. On Hostinger, the app's <code>.env</code> database connection must use MySQL or MariaDB and PHP's PDO MySQL extension must be enabled.</p>

            <div class="action-row">
                <button type="submit" class="btn btn-primary">Download SQL Backup</button>
            </div>
        </form>
    </section>
@endsection
