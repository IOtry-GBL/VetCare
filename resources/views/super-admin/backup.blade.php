@extends('layouts.app')

@section('title', 'Connection and Backup')

@section('sidebar')
    @include('super-admin.sidebar')
@endsection

@section('content')
    <section class="hero-card">
        <div class="hero-row">
            <div>
                <h1 class="hero-title">Connection and Backup</h1>
                <p class="hero-copy">Configure and test external connections, or download a backup of the current database.</p>
            </div>
        </div>
    </section>

    @if (session('success'))
        <div class="alert alert-info" role="status">{{ session('success') }}</div>
    @endif
    @if (session('connection_status'))
        <div class="alert alert-info" role="status">{{ session('connection_status') }}</div>
    @endif
    @if (session('error'))
        <div class="alert banner-danger" role="alert">{{ session('error') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert banner-danger" role="alert">
            <strong>Please correct the connection settings.</strong>
            <ul style="margin: 0.5rem 0 0; padding-left: 1.25rem;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <section class="card">
        <div class="surface-header">
            <div>
                <h2>Connection Settings</h2>
                <p class="section-copy">Use each Test button to check the values currently entered, or save them for later. Credentials are encrypted in storage; these settings do not change the live app connection configured by the server environment.</p>
            </div>
        </div>

        <form action="{{ route('super-admin.backup.connections.update') }}" method="POST" class="stack">
            @csrf

            <div class="action-row" style="justify-content: space-between; align-items: center;">
                <h3>Hostinger / MySQL Database</h3>
                <button type="submit" class="btn btn-secondary" formaction="{{ route('super-admin.backup.connections.test-database') }}" formmethod="POST" formnovalidate name="_method" value="POST">Test Database Connection</button>
            </div>
            <div class="grid" style="grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));">
                <label class="field" for="database_driver">
                    <span class="field-label">SQL driver</span>
                    <select id="database_driver" name="database_driver" class="field-control" required>
                        <option value="mysql" @selected(old('database_driver', $connectionSettings?->database_driver ?? 'mysql') === 'mysql')>MySQL</option>
                        <option value="mariadb" @selected(old('database_driver', $connectionSettings?->database_driver ?? 'mysql') === 'mariadb')>MariaDB</option>
                    </select>
                </label>
                <label class="field" for="database_host">
                    <span class="field-label">Database host</span>
                    <input id="database_host" name="database_host" class="field-control" type="text" value="{{ old('database_host', $connectionSettings?->database_host ?? $databaseDefaults['host']) }}" required maxlength="255" autocomplete="off">
                </label>
                <label class="field" for="database_port">
                    <span class="field-label">Port</span>
                    <input id="database_port" name="database_port" class="field-control" type="number" min="1" max="65535" value="{{ old('database_port', $connectionSettings?->database_port ?? $databaseDefaults['port']) }}" required>
                </label>
                <label class="field" for="database_name">
                    <span class="field-label">Database name</span>
                    <input id="database_name" name="database_name" class="field-control" type="text" value="{{ old('database_name', $connectionSettings?->database_name ?? $databaseDefaults['database']) }}" required maxlength="255" autocomplete="off">
                </label>
                <label class="field" for="database_username">
                    <span class="field-label">Database username</span>
                    <input id="database_username" name="database_username" class="field-control" type="text" value="{{ old('database_username', $connectionSettings?->database_username ?? $databaseDefaults['username']) }}" required maxlength="255" autocomplete="username">
                </label>
                <label class="field" for="database_password">
                    <span class="field-label">Database password</span>
                    <input id="database_password" name="database_password" class="field-control" type="password" value="" maxlength="4096" autocomplete="new-password" placeholder="{{ $connectionSettings?->database_password ? 'Saved; leave blank to keep' : 'Enter database password' }}">
                </label>
            </div>
            @if ($connectionSettings?->database_password)
                <label style="display: inline-flex; align-items: center; gap: 0.5rem;">
                    <input type="checkbox" name="clear_database_password" value="1" @checked(old('clear_database_password'))>
                    <span>Clear the saved database password</span>
                </label>
            @endif

            <div class="action-row" style="justify-content: space-between; align-items: center;">
                <h3>Ollama API</h3>
                <button type="submit" class="btn btn-secondary" formaction="{{ route('super-admin.backup.connections.test-ollama') }}" formmethod="POST" formnovalidate name="_method" value="POST">Test Ollama Connection</button>
            </div>
            <div class="grid" style="grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));">
                <label class="field" for="ollama_base_url">
                    <span class="field-label">Ollama API URL</span>
                    <input id="ollama_base_url" name="ollama_base_url" class="field-control" type="url" value="{{ old('ollama_base_url', $connectionSettings?->ollama_base_url ?? 'http://127.0.0.1:11434') }}" maxlength="2048" placeholder="http://127.0.0.1:11434">
                </label>
                <label class="field" for="ollama_model">
                    <span class="field-label">Default model</span>
                    <input id="ollama_model" name="ollama_model" class="field-control" type="text" value="{{ old('ollama_model', $connectionSettings?->ollama_model) }}" maxlength="255" placeholder="e.g. llama3.1">
                </label>
                <label class="field" for="ollama_api_key">
                    <span class="field-label">API key (if required)</span>
                    <input id="ollama_api_key" name="ollama_api_key" class="field-control" type="password" value="" maxlength="4096" autocomplete="new-password" placeholder="{{ $connectionSettings?->ollama_api_key ? 'Saved; leave blank to keep' : 'Optional' }}">
                </label>
            </div>
            @if ($connectionSettings?->ollama_api_key)
                <label style="display: inline-flex; align-items: center; gap: 0.5rem;">
                    <input type="checkbox" name="clear_ollama_api_key" value="1" @checked(old('clear_ollama_api_key'))>
                    <span>Clear the saved Ollama API key</span>
                </label>
            @endif

            <div class="action-row">
                <button type="submit" class="btn btn-primary" name="_method" value="PUT">Save Connection Settings</button>
            </div>
        </form>
    </section>

    <section class="card" style="max-width: 760px;">
        <div class="surface-header">
            <div>
                <h2>Download Current Database</h2>
                <p class="section-copy">The selected date is used in the backup filename only. The file contains the database configured for this app when you download it and streams directly without using the server's temporary-file directory.</p>
            </div>
        </div>

        <form action="{{ route('super-admin.backup.download') }}" method="POST" class="stack">
            @csrf
            <label class="field" for="backup_date">
                <span class="field-label">Date in backup filename</span>
                <input id="backup_date" name="backup_date" type="date" class="field-control" value="{{ old('backup_date', now()->toDateString()) }}" max="{{ now()->toDateString() }}" required>
            </label>
            <p class="muted-copy">The backup is downloaded as a <code>.sql</code> file. Keep it in a secure location because it contains database information. On Hostinger, the app's <code>.env</code> database connection must use MySQL or MariaDB and PHP's PDO MySQL extension must be enabled.</p>
            <div class="action-row">
                <button type="submit" class="btn btn-primary">Download SQL Backup</button>
            </div>
        </form>
    </section>
@endsection
