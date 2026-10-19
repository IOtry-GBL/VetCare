<?php

use App\Mail\AdherenceNotificationTestMail;
use App\Models\AdherenceNotification;
use App\Models\SuperAdminAdherenceTest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

function makeAdherenceTestingSuperAdmin(): User
{
    return User::factory()->create([
        'role' => 'Staff',
        'is_super_admin' => true,
    ]);
}

test('super admin can send a test-only adherence email to an active pet owner', function () {
    Mail::fake();
    $superAdmin = makeAdherenceTestingSuperAdmin();
    $owner = User::factory()->create([
        'role' => 'Pet Owner',
        'full_name' => 'Test Recipient',
        'email' => 'adherence-recipient@example.test',
        'status' => 'Active',
    ]);

    $this->actingAs($superAdmin)
        ->get(route('super-admin.adherence-tests.index'))
        ->assertOk()
        ->assertViewIs('super-admin.adherence-testing')
        ->assertSee($owner->email);

    $this->actingAs($superAdmin)
        ->post(route('super-admin.adherence-tests.send'), [
            'recipient_user_id' => $owner->user_id,
        ])
        ->assertRedirect(route('super-admin.adherence-tests.index'))
        ->assertSessionHas('success');

    $test = SuperAdminAdherenceTest::query()->sole();
    expect($test->recipient_email)->toBe($owner->email)
        ->and($test->status)->toBe('Sent')
        ->and($test->expires_at->isFuture())->toBeTrue();

    $confirmationUrl = null;
    Mail::assertSent(AdherenceNotificationTestMail::class, function (AdherenceNotificationTestMail $mail) use ($owner, &$confirmationUrl): bool {
        $confirmationUrl = $mail->confirmationUrl;

        return $mail->hasTo($owner->email)
            && str_contains($mail->render(), 'does not sign you in');
    });

    expect($confirmationUrl)->toBeString();
    $this->get($confirmationUrl)
        ->assertOk()
        ->assertSee('Confirm Test Notification');

    expect($test->fresh()->status)->toBe('Sent');
});

test('signed adherence test confirmation records only the test and does not authenticate the recipient', function () {
    $superAdmin = makeAdherenceTestingSuperAdmin();
    $owner = User::factory()->create(['role' => 'Pet Owner']);
    $token = Str::random(64);
    $test = SuperAdminAdherenceTest::query()->create([
        'id' => (string) Str::uuid(),
        'recipient_user_id' => $owner->user_id,
        'created_by' => $superAdmin->user_id,
        'recipient_name' => $owner->full_name,
        'recipient_email' => $owner->email,
        'token_hash' => hash('sha256', $token),
        'status' => 'Sent',
        'expires_at' => now()->addHour(),
    ]);
    $url = URL::temporarySignedRoute('adherence-test.confirm', $test->expires_at, ['token' => $token]);

    $this->post($url)
        ->assertOk()
        ->assertSee('Test notification confirmed successfully');

    expect($test->fresh()->status)->toBe('Confirmed')
        ->and(auth()->check())->toBeFalse()
        ->and(AdherenceNotification::query()->count())->toBe(0);
});

test('unsigned adherence test link cannot confirm even a test record', function () {
    $superAdmin = makeAdherenceTestingSuperAdmin();
    $owner = User::factory()->create(['role' => 'Pet Owner']);
    $token = Str::random(64);
    $test = SuperAdminAdherenceTest::query()->create([
        'id' => (string) Str::uuid(),
        'recipient_user_id' => $owner->user_id,
        'created_by' => $superAdmin->user_id,
        'recipient_name' => $owner->full_name,
        'recipient_email' => $owner->email,
        'token_hash' => hash('sha256', $token),
        'status' => 'Sent',
        'expires_at' => now()->addHour(),
    ]);

    $this->post(route('adherence-test.confirm', ['token' => $token]))
        ->assertForbidden();

    expect($test->fresh()->status)->toBe('Sent');
});

test('regular users cannot access the super admin adherence testing module', function () {
    $user = User::factory()->create(['role' => 'Pet Owner']);

    $this->actingAs($user)
        ->get(route('super-admin.adherence-tests.index'))
        ->assertForbidden();
});

test('adherence test email cannot be sent to an inactive account', function () {
    Mail::fake();
    $superAdmin = makeAdherenceTestingSuperAdmin();
    $owner = User::factory()->create([
        'role' => 'Pet Owner',
        'status' => 'Inactive',
    ]);

    $this->actingAs($superAdmin)
        ->from(route('super-admin.adherence-tests.index'))
        ->post(route('super-admin.adherence-tests.send'), [
            'recipient_user_id' => $owner->user_id,
        ])
        ->assertSessionHasErrors('recipient_user_id');

    Mail::assertNothingSent();
    expect(SuperAdminAdherenceTest::query()->count())->toBe(0);
});
