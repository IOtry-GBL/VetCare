<?php

use App\Models\SystemSetting;
use App\Models\SuperAdminConnectionSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

function makeSuperAdminManagementFixture(): User
{
    return User::create([
        'full_name' => 'System Root',
        'email' => 'root@vetcare.test',
        'password' => Hash::make('password'),
        'contact_no' => '0917-000-0000',
        'role' => 'Staff',
        'status' => 'Active',
        'is_super_admin' => true,
    ]);
}

test('super admin access control page loads', function () {
    $superAdmin = makeSuperAdminManagementFixture();

    $this->actingAs($superAdmin)
        ->get(route('super-admin.access-control'))
        ->assertOk()
        ->assertSee('Roles & Access', false)
        ->assertSee('User Access Overrides')
        ->assertDontSee('href="'.route('super-admin.access-control').'"', false);
});

test('super admin monitoring page loads', function () {
    $superAdmin = makeSuperAdminManagementFixture();

    $this->actingAs($superAdmin)
        ->get(route('super-admin.monitoring'))
        ->assertOk()
        ->assertSee('Monitoring')
        ->assertSee('Recent Activity');
});

test('super admin reports page loads', function () {
    $superAdmin = makeSuperAdminManagementFixture();

    $this->actingAs($superAdmin)
        ->get(route('super-admin.reports'))
        ->assertOk()
        ->assertSee('Clinic Operation Reports')
        ->assertSee('Export CSV');
});

test('super admin settings page can update system settings', function () {
    $superAdmin = makeSuperAdminManagementFixture();

    $response = $this->actingAs($superAdmin)->put(route('super-admin.settings.update'), [
        'clinic_name' => 'Digos Animal Care',
        'clinic_phone' => '0912 555 0101',
        'clinic_address' => '123 Mabini Street',
        'clinic_description' => 'Care for pets of every size.',
        'clinic_email' => 'admin@vetcare.test',
        'timezone' => 'Asia/Manila',
        'appointment_lead_time_hours' => 4,
        'online_booking_enabled' => true,
        'emergency_contact' => '0917-111-2222',
    ]);

    $response->assertRedirect(route('super-admin.settings'));

    expect(SystemSetting::getValue('clinic_name'))->toBe('Digos Animal Care');
    expect(SystemSetting::getValue('clinic_phone'))->toBe('0912 555 0101');
    expect(SystemSetting::getValue('clinic_address'))->toBe('123 Mabini Street');
    expect(SystemSetting::getValue('clinic_description'))->toBe('Care for pets of every size.');
    expect(SystemSetting::getValue('appointment_lead_time_hours'))->toBe(4);
    expect(SystemSetting::getValue('online_booking_enabled'))->toBeTrue();
});

test('super admin settings page provides a separate client email configuration form', function () {
    $superAdmin = makeSuperAdminManagementFixture();

    $this->actingAs($superAdmin)
        ->get(route('super-admin.settings'))
        ->assertOk()
        ->assertSee('Client Email Delivery')
        ->assertSee('name="mail_host"', false)
        ->assertSee('name="mail_password"', false)
        ->assertSee('name="test_email"', false)
        ->assertSee('smtp.gmail.com')
        ->assertSee('Google App Password')
        ->assertSee(route('super-admin.settings.mail.update'), false)
        ->assertSee(route('super-admin.settings.mail.test'), false);
});

test('super admin can configure encrypted SMTP settings for client emails', function () {
    $superAdmin = makeSuperAdminManagementFixture();
    $defaultMailer = config('mail.default');

    $this->actingAs($superAdmin)
        ->put(route('super-admin.settings.mail.update'), [
            'mail_enabled' => '1',
            'mail_host' => 'smtp.example.test',
            'mail_port' => 587,
            'mail_encryption' => 'tls',
            'mail_username' => 'clinic@example.test',
            'mail_password' => 'smtp-private-password',
            'mail_from_address' => 'clinic@example.test',
            'mail_from_name' => 'Digos Veterinary Clinic',
        ])
        ->assertRedirect(route('super-admin.settings'))
        ->assertSessionHas('success');

    $settings = SuperAdminConnectionSetting::query()->findOrFail(1);

    expect($settings->id)->toBe(1)
        ->and($settings->mail_enabled)->toBeTrue()
        ->and($settings->mail_password)->toBe('smtp-private-password')
        ->and(DB::table('super_admin_connection_settings')->value('mail_password'))->not->toBe('smtp-private-password')
        ->and(config('mail.default'))->toBe('smtp')
        ->and(config('mail.mailers.smtp.host'))->toBe('smtp.example.test')
        ->and(config('mail.mailers.smtp.port'))->toBe(587)
        ->and(config('mail.from.address'))->toBe('clinic@example.test');

    $this->actingAs($superAdmin)
        ->put(route('super-admin.settings.mail.update'), [
            'mail_enabled' => '0',
            'mail_encryption' => 'tls',
        ])
        ->assertRedirect(route('super-admin.settings'));

    expect(SuperAdminConnectionSetting::query()->findOrFail(1)->mail_enabled)->toBeFalse()
        ->and(config('mail.default'))->toBe($defaultMailer);
});

test('super admin can send a test email using unsaved SMTP settings', function () {
    $superAdmin = makeSuperAdminManagementFixture();

    \Illuminate\Support\Facades\Mail::fake();

    $this->actingAs($superAdmin)
        ->post(route('super-admin.settings.mail.test'), [
            'mail_host' => 'smtp.example.test',
            'mail_port' => 465,
            'mail_encryption' => 'ssl',
            'mail_username' => 'clinic@example.test',
            'mail_password' => 'smtp-private-password',
            'mail_from_address' => 'clinic@example.test',
            'mail_from_name' => 'Digos Veterinary Clinic',
            'test_email' => 'client@example.test',
        ])
        ->assertRedirect(route('super-admin.settings'))
        ->assertSessionHas('mail_test_success', 'Test email sent successfully to client@example.test.');

    expect(config('mail.mailers.smtp.host'))->toBe('smtp.example.test')
        ->and(config('mail.mailers.smtp.scheme'))->toBe('smtps')
        ->and(config('mail.from.address'))->toBe('clinic@example.test');
});

test('super admin can create a client account', function () {
    $superAdmin = makeSuperAdminManagementFixture();

    $this->actingAs($superAdmin)
        ->post(route('super-admin.users.store'), [
            'full_name' => 'Jamie Owner',
            'email' => 'jamie@example.test',
            'contact_no' => '0917-222-3333',
            'password' => 'password123',
            'role' => 'Pet Owner',
            'status' => 'Active',
        ])
        ->assertRedirect(route('super-admin.users'));

    $this->assertDatabaseHas('users', [
        'email' => 'jamie@example.test',
        'role' => 'Pet Owner',
        'status' => 'Active',
    ]);
});
