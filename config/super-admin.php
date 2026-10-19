<?php

return [
    'roles' => [
        'Pet Owner' => [
            'label' => 'Pet Owner',
            'permissions' => ['pets', 'appointments', 'prescriptions', 'notifications'],
        ],
        'Veterinarian' => [
            'label' => 'Veterinarian',
            'permissions' => ['appointments', 'consultations', 'medical-records', 'prescriptions', 'adherence'],
        ],
        'Staff' => [
            'label' => 'Clinic Staff',
            'permissions' => ['clients', 'pets', 'appointments', 'medical-records', 'reports'],
        ],
        'Admin' => [
            'label' => 'Admin',
            'permissions' => ['users', 'roles', 'monitoring', 'reports', 'settings'],
        ],
        'Super Admin' => [
            'label' => 'Super Admin',
            'permissions' => ['all'],
        ],
    ],

    'settings' => [
        'clinic_name' => 'Digos Veterinary Clinic and Supply',
        'clinic_phone' => '0912 409 5669',
        'clinic_address' => '0740, Corner Mabini, Corregidor, Digos City, 8002, Davao del Sur',
        'clinic_description' => 'Digos Veterinary Clinic is a surgical clinic that accepts hair grooming and spa, boarding, and treatment for both healthy and sick pets. With 27 years of experience in small, exotic, and large animal practice.',
        'clinic_email' => 'clinic@vetcare.test',
        'timezone' => 'Asia/Manila',
        'appointment_lead_time_hours' => 2,
        'online_booking_enabled' => true,
        'emergency_contact' => '0917-000-1234',
    ],
];
