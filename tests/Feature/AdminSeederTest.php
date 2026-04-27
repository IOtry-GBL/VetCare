<?php

use App\Models\User;
use Database\Seeders\AdminSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('admin seeder creates a super admin account', function () {
    $this->seed(AdminSeeder::class);

    $admin = User::where('email', 'admin@vetcare.test')->first();

    expect($admin)->not->toBeNull();
    expect($admin->full_name)->toBe('System Administrator');
    expect($admin->role)->toBe('Staff');
    expect($admin->isSuperAdmin())->toBeTrue();
    expect($admin->status)->toBe('Active');
});
