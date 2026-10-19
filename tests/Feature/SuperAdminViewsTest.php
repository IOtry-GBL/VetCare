<?php

use App\Models\Appointment;
use App\Models\Pet;
use App\Models\SuperAdminConnectionSetting;
use App\Models\User;
use App\Services\ConnectionSettingsTester;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

function makeSuperAdminFixture(): array
{
    $superAdmin = User::factory()->create([
        'role' => 'Staff',
        'is_super_admin' => true,
        'full_name' => 'System Root',
    ]);

    $owner = User::factory()->create([
        'role' => 'Pet Owner',
        'full_name' => 'Jamie Owner',
    ]);

    $vet = User::factory()->create([
        'role' => 'Veterinarian',
        'full_name' => 'Dr. Vera',
    ]);

    $staff = User::factory()->create([
        'role' => 'Staff',
        'full_name' => 'Sam Staff',
    ]);

    $pet = Pet::factory()->create([
        'user_id' => $owner->user_id,
        'pet_name' => 'Pebble',
    ]);

    $appointment = Appointment::factory()->create([
        'pet_id' => $pet->pet_id,
        'status' => 'Pending',
    ]);

    return compact('superAdmin', 'owner', 'vet', 'staff', 'pet', 'appointment');
}

test('super admin dashboard loads', function () {
    $fixture = makeSuperAdminFixture();

    $response = $this->actingAs($fixture['superAdmin'])
        ->get(route('super-admin.dashboard'));

    $response->assertOk()
        ->assertViewIs('super-admin.dashboard')
        ->assertSee('Super Admin Dashboard')
        ->assertSee('Role Switching')
        ->assertSee('Total Users')
        ->assertDontSee('Roles &amp; Access')
        ->assertDontSee(route('super-admin.access-control'), false);
});

test('super admin users page loads', function () {
    $fixture = makeSuperAdminFixture();

    $response = $this->actingAs($fixture['superAdmin'])
        ->get(route('super-admin.users'));

    $response->assertOk()
        ->assertViewIs('super-admin.users')
        ->assertSee('Super Admin Users')
        ->assertSee('Jamie Owner')
        ->assertSee('System Root')
        ->assertDontSee('Roles &amp; Access')
        ->assertDontSee(route('super-admin.access-control'), false);
});

test('super admin analytics page loads', function () {
    $fixture = makeSuperAdminFixture();

    $response = $this->actingAs($fixture['superAdmin'])
        ->get(route('super-admin.analytics'));

    $response->assertOk()
        ->assertViewIs('super-admin.analytics')
        ->assertSee('Super Admin Analytics')
        ->assertSee('Users By Role')
        ->assertSee('Appointments By Status');
});

test('super admin can access the database backup settings', function () {
    $fixture = makeSuperAdminFixture();

    $this->actingAs($fixture['superAdmin'])
        ->get(route('super-admin.backup'))
        ->assertOk()
        ->assertViewIs('super-admin.backup')
        ->assertSee('Download SQL Backup')
        ->assertSee(route('super-admin.backup.download'), false)
        ->assertSee('Test Database Connection')
        ->assertSee('Test Ollama Connection')
        ->assertSee('The selected date is used in the backup filename only.');
});

test('regular users cannot access the super admin database backup', function () {
    $user = User::factory()->create(['role' => 'Admin']);

    $this->actingAs($user)
        ->get(route('super-admin.backup'))
        ->assertForbidden();
});

test('super admin can save encrypted database and Ollama connection details without changing the live database config', function () {
    $fixture = makeSuperAdminFixture();
    $liveDatabase = config('database.connections.mysql.database');

    $this->actingAs($fixture['superAdmin'])
        ->post(route('super-admin.backup.connections.update'), [
            '_method' => 'PUT',
            'database_driver' => 'mysql',
            'database_host' => 'mysql.example.test',
            'database_port' => 3306,
            'database_name' => 'clinic_db',
            'database_username' => 'clinic_user',
            'database_password' => 'mysql-secret',
            'ollama_base_url' => 'http://127.0.0.1:11434',
            'ollama_api_key' => 'ollama-secret',
            'ollama_model' => 'llama3.1',
        ])
        ->assertRedirect(route('super-admin.backup'));

    $settings = SuperAdminConnectionSetting::query()->findOrFail(1);
    expect($settings->database_password)->toBe('mysql-secret')
        ->and($settings->ollama_api_key)->toBe('ollama-secret')
        ->and($settings->ollama_base_url)->toBe('http://127.0.0.1:11434')
        ->and(DB::table('super_admin_connection_settings')->value('database_password'))->not->toBe('mysql-secret')
        ->and(DB::table('super_admin_connection_settings')->value('ollama_api_key'))->not->toBe('ollama-secret')
        ->and(config('database.connections.mysql.database'))->toBe($liveDatabase);

    $this->actingAs($fixture['superAdmin'])
        ->get(route('super-admin.backup'))
        ->assertOk()
        ->assertDontSee('mysql-secret')
        ->assertDontSee('ollama-secret');
});

test('super admin can test the saved database connection', function () {
    $fixture = makeSuperAdminFixture();
    SuperAdminConnectionSetting::query()->create([
        'id' => 1,
        'database_driver' => 'mysql',
        'database_host' => 'mysql.example.test',
        'database_port' => 3306,
        'database_name' => 'clinic_db',
        'database_username' => 'clinic_user',
        'database_password' => 'mysql-secret',
    ]);

    $tester = Mockery::mock(ConnectionSettingsTester::class);
    $tester->shouldReceive('testDatabase')
        ->once()
        ->with(Mockery::on(fn (SuperAdminConnectionSetting $settings): bool => $settings->database_host === 'mysql.example.test'));
    $this->app->instance(ConnectionSettingsTester::class, $tester);

    $this->actingAs($fixture['superAdmin'])
        ->post(route('super-admin.backup.connections.test-database'))
        ->assertRedirect(route('super-admin.backup'))
        ->assertSessionHas('connection_status', 'Connection Successful');
});

test('super admin can test unsaved database connection settings from the form', function () {
    $fixture = makeSuperAdminFixture();

    $tester = Mockery::mock(ConnectionSettingsTester::class);
    $tester->shouldReceive('testDatabase')
        ->once()
        ->with(Mockery::on(fn (SuperAdminConnectionSetting $settings): bool => $settings->database_host === 'hostinger.example.test'
            && $settings->database_name === 'clinic_db'
            && $settings->database_password === 'temporary-secret'));
    $this->app->instance(ConnectionSettingsTester::class, $tester);

    $this->actingAs($fixture['superAdmin'])
        ->post(route('super-admin.backup.connections.test-database'), [
            'database_driver' => 'mysql',
            'database_host' => 'hostinger.example.test',
            'database_port' => 3306,
            'database_name' => 'clinic_db',
            'database_username' => 'clinic_user',
            'database_password' => 'temporary-secret',
        ])
        ->assertRedirect(route('super-admin.backup'))
        ->assertSessionHas('connection_status', 'Connection Successful');
});

test('super admin can test unsaved Ollama settings from the form', function () {
    $fixture = makeSuperAdminFixture();

    Http::fake([
        'https://ollama.example.test/api/tags' => Http::response(['models' => []]),
    ]);

    $this->actingAs($fixture['superAdmin'])
        ->post(route('super-admin.backup.connections.test-ollama'), [
            'ollama_base_url' => 'https://ollama.example.test',
            'ollama_api_key' => 'temporary-secret',
        ])
        ->assertRedirect(route('super-admin.backup'))
        ->assertSessionHas('connection_status', 'Connection Successful');

    Http::assertSent(fn (Request $request): bool => $request->url() === 'https://ollama.example.test/api/tags'
        && $request->hasHeader('Authorization'));
});

test('super admin can test the saved Ollama API connection', function () {
    $fixture = makeSuperAdminFixture();
    SuperAdminConnectionSetting::query()->create([
        'id' => 1,
        'database_driver' => 'mysql',
        'database_host' => 'mysql.example.test',
        'database_port' => 3306,
        'database_name' => 'clinic_db',
        'database_username' => 'clinic_user',
        'ollama_base_url' => 'http://127.0.0.1:11434',
        'ollama_api_key' => 'ollama-secret',
    ]);

    Http::fake([
        'http://127.0.0.1:11434/api/tags' => Http::response([
            'models' => [
                ['name' => 'llama3.1'],
            ],
        ]),
    ]);

    $this->actingAs($fixture['superAdmin'])
        ->post(route('super-admin.backup.connections.test-ollama'))
        ->assertRedirect(route('super-admin.backup'))
        ->assertSessionHas('connection_status', 'Connection Successful');

    Http::assertSent(fn (Request $request): bool => $request->url() === 'http://127.0.0.1:11434/api/tags'
        && $request->hasHeader('Authorization', 'Bearer ollama-secret'));
});
