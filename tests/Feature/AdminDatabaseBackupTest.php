<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function makeAdminDatabaseBackupUser(): User
{
    return User::factory()->create([
        'role' => 'Staff',
        'is_super_admin' => true,
        'impersonating_role' => 'admin',
        'full_name' => 'Backup Admin',
    ]);
}

test('admin can open the database backup settings page', function () {
    $this->actingAs(makeAdminDatabaseBackupUser())
        ->get(route('admin.backup'))
        ->assertOk()
        ->assertViewIs('admin.backup')
        ->assertSee('Download SQL Backup')
        ->assertSee('name="backup_date"', false)
        ->assertSee('The selected date is used in the backup filename only.');
});

test('admin can download the current database with a selected filename date', function () {
    $response = $this->actingAs(makeAdminDatabaseBackupUser())
        ->post(route('admin.backup.download'), [
            'backup_date' => '2026-10-14',
        ])
        ->assertOk()
        ->assertDownload('database_backup_2026-10-14.sql')
        ->assertHeader('Content-Type', 'application/sql; charset=UTF-8')
        ->assertHeader('X-Accel-Buffering', 'no');

    $content = $response->streamedContent();

    expect($content)
        ->toContain('-- Filename date selected: 2026-10-14')
        ->toContain('CREATE TABLE')
        ->toContain('Backup Admin');

    $restoredDatabase = new PDO('sqlite::memory:');
    $restoredDatabase->exec($content);
    expect($restoredDatabase->query("SELECT full_name FROM users WHERE full_name = 'Backup Admin'")->fetchColumn())
        ->toBe('Backup Admin');
    expect($restoredDatabase->query("SELECT name FROM sqlite_master WHERE type = 'index' AND name = 'sessions_last_activity_index'")->fetchColumn())
        ->toBe('sessions_last_activity_index');
});

test('admin database backup rejects a future filename date', function () {
    $this->actingAs(makeAdminDatabaseBackupUser())
        ->from(route('admin.backup'))
        ->post(route('admin.backup.download'), [
            'backup_date' => now()->addDay()->toDateString(),
        ])
        ->assertRedirect(route('admin.backup'))
        ->assertSessionHasErrors('backup_date');
});
