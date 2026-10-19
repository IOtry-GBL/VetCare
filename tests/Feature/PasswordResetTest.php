<?php

use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Symfony\Component\Mailer\Exception\TransportException;

uses(RefreshDatabase::class);

test('login page links to password recovery', function () {
    $this->get(route('login'))
        ->assertOk()
        ->assertSee(route('password.request'), false)
        ->assertSee('Forgot password?')
        ->assertSee('Digos Veterinary Clinic and Supply')
        ->assertSee('0912 409 5669')
        ->assertSee('Digos City, 8002, Davao del Sur');
});

test('login page uses the clinic profile saved by super admin', function () {
    SystemSetting::setValue('clinic_name', 'Digos Animal Care');
    SystemSetting::setValue('clinic_phone', '0912 555 0101');
    SystemSetting::setValue('clinic_address', '123 Mabini Street');
    SystemSetting::setValue('clinic_description', 'Care for pets of every size.');

    $this->get(route('login'))
        ->assertOk()
        ->assertSee('Digos Animal Care')
        ->assertSee('0912 555 0101')
        ->assertSee('123 Mabini Street')
        ->assertSee('Care for pets of every size.');
});

test('password reset request sends a reset notification for a known email', function () {
    Notification::fake();
    config(['mail.default' => 'smtp']);
    $user = User::factory()->create(['email' => 'reset@example.test']);

    $this->post(route('password.email'), ['email' => $user->email])
        ->assertRedirect()
        ->assertSessionHas('status', 'If an account exists for that email address, a password reset link has been sent.');

    Notification::assertSentTo($user, ResetPassword::class, function (ResetPassword $notification) use ($user): bool {
        $url = parse_url($notification->toMail($user)->actionUrl);

        if (! is_array($url)) {
            return false;
        }

        parse_str($url['query'] ?? '', $query);

        return isset($url['path'])
            && str_ends_with($url['path'], '/reset-password/'.$notification->token)
            && ($query['email'] ?? null) === $user->email;
    });
});

test('password reset request reports mail delivery failures', function () {
    config(['mail.default' => 'smtp']);
    Password::shouldReceive('sendResetLink')
        ->once()
        ->andThrow(new TransportException('SMTP connection failed'));

    $this->from(route('password.request'))
        ->post(route('password.email'), ['email' => 'reset@example.test'])
        ->assertRedirect(route('password.request'))
        ->assertSessionHas('mail_error', 'We could not send the reset email right now. Please try again later or contact the clinic.');
});

test('password reset request uses the same response for an unknown email', function () {
    Notification::fake();
    config(['mail.default' => 'smtp']);

    $this->post(route('password.email'), ['email' => 'unknown@example.test'])
        ->assertRedirect()
        ->assertSessionHas('status', 'If an account exists for that email address, a password reset link has been sent.');

    Notification::assertNothingSent();
});

test('password reset request explains when the mailer cannot deliver email', function () {
    config(['mail.default' => 'log']);

    $this->from(route('password.request'))
        ->post(route('password.email'), ['email' => 'reset@example.test'])
        ->assertRedirect(route('password.request'))
        ->assertSessionHas('mail_error', 'Email delivery is not configured. Ask an administrator to enable SMTP email settings, then try again.');
});

test('a valid reset token changes the password and consumes the token', function () {
    $user = User::factory()->create([
        'email' => 'reset@example.test',
        'password' => 'old-password',
    ]);
    $token = Password::createToken($user);

    $this->get(route('password.reset', ['token' => $token, 'email' => $user->email]))
        ->assertOk()
        ->assertViewIs('auth.reset-password')
        ->assertSee('name="token"', false);

    $this->post(route('password.update'), [
        'token' => $token,
        'email' => $user->email,
        'password' => 'new-password-123',
        'password_confirmation' => 'new-password-123',
    ])
        ->assertRedirect(route('login'))
        ->assertSessionHas('status', 'Your password has been reset. You can now sign in.');

    expect(Hash::check('new-password-123', $user->fresh()->password))->toBeTrue();
    expect(Password::tokenExists($user->fresh(), $token))->toBeFalse();
});

test('invalid or expired reset tokens do not change the password', function () {
    $user = User::factory()->create([
        'email' => 'reset@example.test',
        'password' => 'old-password',
    ]);

    $this->post(route('password.update'), [
        'token' => 'invalid-token',
        'email' => $user->email,
        'password' => 'new-password-123',
        'password_confirmation' => 'new-password-123',
    ])
        ->assertSessionHasErrors('email');

    expect(Hash::check('old-password', $user->fresh()->password))->toBeTrue();
});
