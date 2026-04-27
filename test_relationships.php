<?php
// Direct database test
$conn = new \PDO('mysql:host=localhost:3306;dbname=ai_vet_clinic', 'root', 'root', [
    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
]);

// Create test data
$stmt = $conn->prepare("
    INSERT INTO users (full_name, email, contact_no, password, role, status, created_at, updated_at)
    VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())
");
$stmt->execute([
    'Test Pet Owner',
    'owner@example.com',
    '1234567890',
    password_hash('password123', PASSWORD_BCRYPT),
    'Pet Owner',
    'Active'
]);
$userId = $conn->lastInsertId();

// Create a pet
$stmt = $conn->prepare("
    INSERT INTO pets (user_id, pet_name, species, breed, sex, date_of_birth, weight, created_at, updated_at)
    VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
");
$stmt->execute([
    $userId,
    'Fluffy',
    'Cat',
    'Persian',
    'Female',
    '2020-01-15',
    4.5
]);
$petId = $conn->lastInsertId();

// Create an appointment
$stmt = $conn->prepare("
    INSERT INTO appointments (pet_id, appointment_date, appointment_time, consultation_mode, reason_for_visit, status, created_at, updated_at)
    VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())
");
$stmt->execute([
    $petId,
    date('Y-m-d', strtotime('+5 days')),
    '10:00:00',
    'In-clinic',
    'Regular checkup',
    'Pending'
]);
$appointmentId = $conn->lastInsertId();

// Create a consultation
$stmt = $conn->prepare("
    INSERT INTO users (full_name, email, contact_no, password, role, status, created_at, updated_at)
    VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())
");
$stmt->execute([
    'Dr. Vet',
    'vet@example.com',
    '9876543210',
    password_hash('password123', PASSWORD_BCRYPT),
    'Veterinarian',
    'Active'
]);
$vetId = $conn->lastInsertId();

$stmt = $conn->prepare("
    INSERT INTO consultations (appointment_id, veterinarian_id, chief_complaint, consultation_date, status, created_at, updated_at)
    VALUES (?, ?, ?, NOW(), ?, NOW(), NOW())
");
$stmt->execute([
    $appointmentId,
    $vetId,
    'Checkup',
    'Open'
]);
$consultationId = $conn->lastInsertId();

// Create medical record
$stmt = $conn->prepare("
    INSERT INTO medical_records (pet_id, consultation_id, diagnosis, created_at, updated_at)
    VALUES (?, ?, ?, NOW(), NOW())
");
$stmt->execute([
    $petId,
    $consultationId,
    'Healthy'
]);
$recordId = $conn->lastInsertId();

// Create prescription
$stmt = $conn->prepare("
    INSERT INTO e_prescriptions (record_id, medication_name, dosage, frequency, duration, issued_at)
    VALUES (?, ?, ?, ?, ?, NOW())
");
$stmt->execute([
    $recordId,
    'Amoxicillin',
    '500mg',
    'Twice daily',
    '7 days'
]);

echo "✓ Test data created successfully!\n";

// Now test the queries that were failing
echo "\n✓ Testing dashboard queries:\n";

// Test 1: Get user's upcoming appointments
$stmt = $conn->prepare("
    SELECT a.* FROM appointments a
    WHERE EXISTS (
        SELECT * FROM pets WHERE a.pet_id = pets.pet_id AND pets.user_id = ?
    )
    AND a.status != 'Cancelled'
    ORDER BY a.appointment_date
    LIMIT 4
");
$stmt->execute([$userId]);
$appointments = $stmt->fetchAll(\PDO::FETCH_ASSOC);
echo "  - Upcoming appointments found: " . count($appointments) . "\n";

// Test 2: Get user's prescriptions (the one that was throwing the error)
$stmt = $conn->prepare("
    SELECT p.* FROM e_prescriptions p
    WHERE EXISTS (
        SELECT * FROM medical_records mr 
        WHERE p.record_id = mr.record_id
        AND EXISTS (
            SELECT * FROM consultations c
            WHERE mr.consultation_id = c.consultation_id
            AND EXISTS (
                SELECT * FROM appointments a
                WHERE c.appointment_id = a.appointment_id
                AND EXISTS (
                    SELECT * FROM pets
                    WHERE a.pet_id = pets.pet_id AND pets.user_id = ?
                )
            )
        )
    )
    LIMIT 4
");
$stmt->execute([$userId]);
$prescriptions = $stmt->fetchAll(\PDO::FETCH_ASSOC);
echo "  - Prescriptions found: " . count($prescriptions) . "\n";

// Test 3: Get user's pets
$stmt = $conn->prepare("SELECT * FROM pets WHERE user_id = ?");
$stmt->execute([$userId]);
$pets = $stmt->fetchAll(\PDO::FETCH_ASSOC);
echo "  - Pets found: " . count($pets) . "\n";

echo "\n✓✓✓ All relationship tests passed! ✓✓✓\n";
