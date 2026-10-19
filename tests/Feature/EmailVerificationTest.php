<?php

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

test('new pet owners must verify their email before accessing their account', function () {
    Notification::fake();

    $response = $this->post(route('register'), [
        'full_name' => 'Unverified Owner',
        'email' => 'unverified-owner@example.com',
        'contact_no' => '09170000000',
        'address' => '12 Pet Lane, Springvale',
        'password' => 'CedarMoss47!',
        'password_confirmation' => 'CedarMoss47!',
    ]);

    $user = User::where('email', 'unverified-owner@example.com')->firstOrFail();

    $response->assertRedirect(route('verification.notice'));
    $this->assertAuthenticatedAs($user);
    $this->assertNull($user->email_verified_at);
    $this->assertSame('12 Pet Lane, Springvale', $user->address);
    $this->assertDatabaseHas('users', [
        'user_id' => $user->user_id,
        'address' => '12 Pet Lane, Springvale',
    ]);
    Notification::assertSentTo($user, VerifyEmail::class);

    $this->get(route('pet-owner.dashboard'))
        ->assertRedirect(route('verification.notice'));
});

test('a pet owner can verify their email through the signed link', function () {
    $user = User::factory()->unverified()->create([
        'role' => 'Pet Owner',
    ]);

    $verificationUrl = \Illuminate\Support\Facades\URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(30),
        ['id' => $user->getKey(), 'hash' => sha1($user->getEmailForVerification())]
    );

    $this->actingAs($user)
        ->get($verificationUrl)
        ->assertRedirect('/pet-owner/dashboard');

    expect($user->fresh()->hasVerifiedEmail())->toBeTrue();
});
