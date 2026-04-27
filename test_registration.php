<?php
// Direct database test without loading full Laravel
$conn = new \PDO('mysql:host=localhost:3306;dbname=ai_vet_clinic', 'root', 'root', [
    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
]);

// Insert a test user
$stmt = $conn->prepare("
    INSERT INTO users (full_name, email, contact_no, password, role, status, created_at, updated_at)
    VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())
");

$stmt->execute([
    'Test User',
    'testuser@example.com',
    '1234567890',
    password_hash('password123', PASSWORD_BCRYPT),
    'Pet Owner',
    'Active'
]);

$userId = $conn->lastInsertId();

// Verify the user was created
$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch(\PDO::FETCH_ASSOC);

if ($user) {
    echo "✓ User created successfully!\n";
    echo "  User ID: " . $user['user_id'] . "\n";
    echo "  Name: " . $user['full_name'] . "\n";
    echo "  Email: " . $user['email'] . "\n";
    echo "  Contact: " . $user['contact_no'] . "\n";
    echo "  Role: " . $user['role'] . "\n";
    echo "  Status: " . $user['status'] . "\n";
    echo "\n✓✓✓ Database and role enum validation successful! ✓✓✓\n";
} else {
    echo "✗ Failed to create user\n";
}
