<?php

namespace App\Http\Controllers;

use App\Models\SuperAdminConnectionSetting;
use App\Services\AdminDatabaseBackupService;
use Illuminate\Http\Request;
use RuntimeException;

class DatabaseBackupController extends Controller
{
    /**
     * Display the database backup page for the current admin area.
     */
    public function index(Request $request)
    {
        if ($request->routeIs('super-admin.*')) {
            return view('super-admin.backup', [
                'connectionSettings' => SuperAdminConnectionSetting::query()->find(1),
                'databaseDefaults' => config('database.connections.mysql'),
            ]);
        }

        return view('admin.backup');
    }

    /**
     * Download a backup of the current database using the selected filename date.
     */
    public function download(Request $request, AdminDatabaseBackupService $backupService)
    {
        $validated = $request->validate([
            'backup_date' => ['required', 'date_format:Y-m-d', 'before_or_equal:today'],
        ]);

        return response()->streamDownload(function () use ($backupService, $validated): void {
            $output = fopen('php://output', 'wb');
            if ($output === false) {
                throw new RuntimeException('Unable to open the streamed database backup output.');
            }

            try {
                $backupService->writeTo($output, $validated['backup_date']);
            } finally {
                fclose($output);
            }
        }, 'database_backup_'.$validated['backup_date'].'.sql', [
            'Content-Type' => 'application/sql; charset=UTF-8',
            'Cache-Control' => 'private, no-store, no-cache, must-revalidate',
            'X-Accel-Buffering' => 'no',
        ]);
    }
}
