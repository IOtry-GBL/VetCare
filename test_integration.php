<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Test User model
$user = \App\Models\User::factory()->create();
echo "✓ User created: " . $user->full_name . " (" . $user->email . ")\n";

// Test Pet model
$pet = \App\Models\Pet::factory()->create(['user_id' => $user->user_id]);
echo "✓ Pet created: " . $pet->pet_name . " (" . $pet->species . ")\n";

// Test Appointment model
$appointment = \App\Models\Appointment::factory()->create(['pet_id' => $pet->pet_id]);
echo "✓ Appointment created for pet: " . $appointment->pet->pet_name . "\n";

// Test relationships
echo "\n✓ All relationships working:\n";
echo "  - User has " . $user->pets()->count() . " pets\n";
echo "  - Pet belongs to user: " . $pet->owner->full_name . "\n";
echo "  - Appointment belongs to pet: " . $appointment->pet->pet_name . "\n";

echo "\n✓✓✓ Database integration successful! ✓✓✓\n";
