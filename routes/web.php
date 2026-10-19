<?php

use App\Http\Controllers\AdherenceController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\DatabaseBackupController;
use App\Http\Controllers\PetOwnerController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\SuperAdminAdherenceTestController;
use App\Http\Controllers\SuperAdminConnectionSettingsController;
use App\Http\Controllers\SuperAdminController;
use App\Http\Controllers\SuperAdminMailSettingsController;
use App\Http\Controllers\VeterinarianController;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public Routes
Route::get('/', [LoginController::class, 'show'])->name('home');

Route::get('/login', [LoginController::class, 'show'])
    ->name('login')->middleware('guest');

Route::post('/login', [LoginController::class, 'store'])
    ->middleware('guest');

Route::middleware('guest')->group(function () {
    Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])
        ->name('password.request');
    Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('password.email');
    Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])
        ->name('password.reset');
    Route::post('/reset-password', [NewPasswordController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('password.update');
});

Route::get('/adherence-test/{token}', [SuperAdminAdherenceTestController::class, 'show'])
    ->middleware(['signed', 'throttle:30,1'])
    ->name('adherence-test.show');
Route::post('/adherence-test/{token}', [SuperAdminAdherenceTestController::class, 'confirm'])
    ->middleware(['signed', 'throttle:6,1'])
    ->name('adherence-test.confirm');

Route::get('/register', function () {
    return view('auth.register');
})->name('register')->middleware('guest');

Route::post('/register', [RegisterController::class, 'store'])
    ->middleware('guest');

Route::middleware('auth')->group(function () {
    Route::get('/email/verify', function () {
        return view('auth.verify-email');
    })->name('verification.notice');

    Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect('/pet-owner/dashboard');
        }

        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));
        }

        return redirect('/pet-owner/dashboard')->with('success', 'Your email address has been verified.');
    })->middleware(['signed', 'throttle:6,1'])->name('verification.verify');

    Route::post('/email/verification-notification', function (Request $request) {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect('/pet-owner/dashboard');
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with('status', 'verification-link-sent');
    })->middleware('throttle:6,1')->name('verification.send');

    Route::get('/email/verification-notification', function () {
        return redirect()->route('verification.notice');
    })->name('verification.send.get');
});

Route::post('/logout', function () {
    auth()->logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return redirect('/');
})->middleware('auth')->name('logout');

// Authenticated Routes with Role-Based Access
Route::middleware('auth')->group(function () {
    Route::middleware('pet-owner.verified')->group(function () {
        Route::get('/profile/settings', [ProfileController::class, 'edit'])->name('profile.settings');
        Route::put('/profile/settings', [ProfileController::class, 'update'])->name('profile.settings.update');
    });

    // Pet Owner Routes
    Route::middleware(['role:owner', 'pet-owner.verified'])->prefix('pet-owner')->name('pet-owner.')->group(function () {
        Route::get('/dashboard', [PetOwnerController::class, 'dashboard'])->name('dashboard');
        Route::get('/pets', [PetOwnerController::class, 'pets'])->name('pets');
        Route::get('/pets/create', [PetOwnerController::class, 'createPet'])->name('pets.create');
        Route::post('/pets', [PetOwnerController::class, 'storePet'])->name('pets.store');
        Route::get('/pets/{id}', [PetOwnerController::class, 'showPet'])->name('pets.show');
        Route::get('/pets/{id}/edit', [PetOwnerController::class, 'editPet'])->name('pets.edit');
        Route::put('/pets/{id}', [PetOwnerController::class, 'updatePet'])->name('pets.update');
        Route::get('/pets/{id}/confirm-delete', [PetOwnerController::class, 'confirmDeletePet'])->name('pets.confirm-delete');
        Route::delete('/pets/{id}', [PetOwnerController::class, 'deletePet'])->name('pets.delete');
        Route::get('/symptom-checker', [PetOwnerController::class, 'symptomChecker'])->name('symptom-checker');
        Route::post('/symptom-checker', [PetOwnerController::class, 'submitSymptomGuidance'])->name('symptom-checker.submit');
        Route::get('/appointments', [PetOwnerController::class, 'appointments'])->name('appointments');
        Route::get('/appointments/book', [PetOwnerController::class, 'bookAppointment'])->name('appointments.book');
        Route::post('/appointments', [PetOwnerController::class, 'storeAppointmentRequest'])->name('appointments.store');
        Route::get('/appointments/available-times', [PetOwnerController::class, 'getAvailableTimes'])->name('appointments.available-times');
        Route::get('/appointments/{id}', [PetOwnerController::class, 'showAppointment'])->name('appointments.show');
        Route::delete('/appointments/{id}/cancel', [PetOwnerController::class, 'cancelAppointment'])->name('appointments.cancel');
        Route::put('/appointments/{id}/reschedule', [PetOwnerController::class, 'rescheduleAppointment'])->name('appointments.reschedule');
        Route::get('/prescriptions', [PetOwnerController::class, 'prescriptions'])->name('prescriptions');
        Route::get('/notifications', [PetOwnerController::class, 'notifications'])->name('notifications');
        Route::get('/notifications/{id}', [PetOwnerController::class, 'showNotification'])->name('notifications.show');
        Route::post('/notifications/{id}/mark-read', [PetOwnerController::class, 'markNotificationAsRead'])->name('notifications.mark-read');
        Route::post('/notifications/preferences', [PetOwnerController::class, 'saveNotificationPreferences'])->name('notifications.preferences');
        Route::delete('/notifications', [PetOwnerController::class, 'deleteNotification'])->name('notifications.delete');
        Route::delete('/notifications/clear-all', [PetOwnerController::class, 'clearAllNotifications'])->name('notifications.clear-all');
        Route::get('/adherence/{id}', [PetOwnerController::class, 'viewAdherence'])->name('adherence.view');
        Route::delete('/adherence/{id}/delete', [PetOwnerController::class, 'deleteAdherence'])->name('adherence.delete');
    });

    // Veterinarian Routes
    Route::middleware('role:vet')->prefix('vet')->name('vet.')->group(function () {
        Route::get('/dashboard', [VeterinarianController::class, 'dashboard'])->name('dashboard');
        Route::get('/appointments', [VeterinarianController::class, 'appointments'])->name('appointments');
        Route::get('/appointments/create', [VeterinarianController::class, 'createAppointment'])->name('appointments.create');
        Route::post('/appointments', [VeterinarianController::class, 'storeAppointment'])->name('appointments.store');
        Route::get('/appointments/{appointment}/session', [VeterinarianController::class, 'showAppointmentSession'])->name('appointments.session');
        Route::post('/appointments/{appointment}/session', [VeterinarianController::class, 'storeAppointmentSession'])->name('appointments.session.store');
        Route::post('/appointments/{appointment}/dna', [VeterinarianController::class, 'markAppointmentDidNotArrive'])->name('appointments.dna');
        Route::get('/medical-records', [VeterinarianController::class, 'medicalRecords'])->name('medical-records');
        Route::get('/medical-records/{record}', [VeterinarianController::class, 'medicalRecordDetails'])->name('medical-records.details');
        Route::get('/prescriptions', [VeterinarianController::class, 'prescriptions'])->name('prescriptions');
        Route::post('/prescriptions', [VeterinarianController::class, 'storePrescription'])->name('prescriptions.store');
        Route::get('/adherence-monitoring', [VeterinarianController::class, 'adherenceMonitoring'])->name('adherence-monitoring');
        Route::post('/adherence/{prescription}/update', [VeterinarianController::class, 'updateAdherence'])->name('adherence.update');
    });

    // Staff Routes
    Route::middleware('role:staff')->prefix('staff')->name('staff.')->group(function () {
        // Dashboard and Core
        Route::get('/dashboard', [StaffController::class, 'dashboard'])->name('dashboard');
        Route::get('/queue', [StaffController::class, 'queue'])->name('queue');
        Route::get('/notifications', [StaffController::class, 'notifications'])->name('notifications');
        Route::get('/notifications/feed', [StaffController::class, 'notificationsFeed'])->name('notifications.feed');
        Route::delete('/notifications/clear-all', [StaffController::class, 'clearAllNotifications'])->name('notifications.clear-all');
        Route::delete('/notifications/{type}/{id}', [StaffController::class, 'deleteNotification'])->name('notifications.delete');

        // Appointment Management (Section 2.1)
        Route::get('/appointments/create', [StaffController::class, 'createAppointment'])->name('appointments.create');
        Route::post('/appointments', [StaffController::class, 'storeAppointment'])->name('appointments.store');
        Route::get('/appointments/pending', [StaffController::class, 'pendingAppointments'])->name('appointments.pending');
        Route::get('/appointments/{appointment}/confirm', [StaffController::class, 'confirmAppointment'])->name('appointments.confirm');
        Route::post('/appointments/{appointment}/approve', [StaffController::class, 'approveAppointment'])->name('appointments.approve');
        Route::delete('/appointments/{appointment}/reject', [StaffController::class, 'rejectAppointment'])->name('appointments.reject');

        // Patient Records Management (Section 2.2)
        Route::get('/patients', [StaffController::class, 'patients'])->name('patients');
        Route::get('/patients/register', [StaffController::class, 'registerPatient'])->name('patients.register');
        Route::post('/patients', [StaffController::class, 'storePatient'])->name('patients.store');
        Route::get('/patients/{pet}/details', [StaffController::class, 'patientDetails'])->name('patients.details');
        Route::get('/patients/{pet}/edit', [StaffController::class, 'editPatient'])->name('patients.edit');
        Route::put('/patients/{pet}', [StaffController::class, 'updatePatient'])->name('patients.update');

        // Client Management
        Route::get('/clients', [StaffController::class, 'clients'])->name('clients');
        Route::get('/clients/register', [StaffController::class, 'registerClient'])->name('register-client');
        Route::post('/clients', [StaffController::class, 'storeClient'])->name('store-client');

        // Pet Management
        Route::get('/pets', [StaffController::class, 'pets'])->name('pets');
        Route::get('/pets/add', [StaffController::class, 'createPet'])->name('add-pet');
        Route::post('/pets', [StaffController::class, 'storePet'])->name('store-pet');
        Route::get('/pets/{id}/edit', [StaffController::class, 'editPet'])->name('pets.edit');
        Route::put('/pets/{id}', [StaffController::class, 'updatePet'])->name('pets.update');
        Route::get('/pets/{id}/confirm-delete', [StaffController::class, 'confirmDeletePet'])->name('pets.confirm-delete');
        Route::delete('/pets/{id}', [StaffController::class, 'deletePet'])->name('pets.delete');

        // E-prescriptions and Medical Records (Section 2.3)
        Route::get('/prescriptions', [StaffController::class, 'prescriptions'])->name('prescriptions');
        Route::get('/prescriptions/{record}', [StaffController::class, 'prescriptionDetails'])->name('prescriptions.details');
        Route::get('/medical-records', [StaffController::class, 'medicalRecords'])->name('medical-records');
        Route::get('/medical-records/{record}', [StaffController::class, 'medicalRecordDetails'])->name('medical-records.details');

        // Reporting (Section 2.5)
        Route::get('/reports', [StaffController::class, 'reports'])->name('reports');
        Route::get('/reports/appointments', [StaffController::class, 'appointmentReport'])->name('reports.appointments');
        Route::get('/reports/prescriptions', [StaffController::class, 'prescriptionReport'])->name('reports.prescriptions');
        Route::get('/reports/appointments/export', [StaffController::class, 'exportAppointmentReport'])->name('reports.appointments.export');
    });

    // Admin Routes
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
        Route::get('/users', [AdminController::class, 'users'])->name('users');
        Route::post('/users', [AdminController::class, 'storeUser'])->name('users.store');
        Route::put('/users/{id}', [AdminController::class, 'updateUser'])->name('users.update');
        Route::delete('/users/{id}', [AdminController::class, 'deleteUser'])->name('users.delete');
        Route::get('/pets', [AdminController::class, 'pets'])->name('pets');
        Route::get('/analytics', [AdminController::class, 'analytics'])->name('analytics');
        Route::get('/backup', [DatabaseBackupController::class, 'index'])->name('backup');
        Route::post('/backup', [DatabaseBackupController::class, 'download'])->name('backup.download');
    });

    // Super Admin Routes
    Route::middleware('super-admin')->prefix('super-admin')->name('super-admin.')->group(function () {
        Route::get('/dashboard', [SuperAdminController::class, 'dashboard'])->name('dashboard');
        Route::post('/switch-role', [SuperAdminController::class, 'switchRole'])->name('switch-role');
        Route::post('/reset', [SuperAdminController::class, 'resetToSuperAdmin'])->name('reset');
        Route::get('/users', [SuperAdminController::class, 'users'])->name('users');
        Route::post('/users', [SuperAdminController::class, 'storeUser'])->name('users.store');
        Route::put('/users/{user}', [SuperAdminController::class, 'updateUser'])->name('users.update');
        Route::delete('/users/{user}', [SuperAdminController::class, 'deleteUser'])->name('users.delete');
        Route::get('/access-control', [SuperAdminController::class, 'accessControl'])->name('access-control');
        Route::put('/access-control/{user}', [SuperAdminController::class, 'updateAccess'])->name('access-control.update');
        Route::get('/monitoring', [SuperAdminController::class, 'monitoring'])->name('monitoring');
        Route::get('/reports', [SuperAdminController::class, 'reports'])->name('reports');
        Route::get('/reports/export', [SuperAdminController::class, 'exportReports'])->name('reports.export');
        Route::get('/settings', [SuperAdminController::class, 'settings'])->name('settings');
        Route::put('/settings', [SuperAdminController::class, 'updateSettings'])->name('settings.update');
        Route::put('/settings/mail', [SuperAdminMailSettingsController::class, 'update'])->name('settings.mail.update');
        Route::post('/settings/mail/test', [SuperAdminMailSettingsController::class, 'test'])->name('settings.mail.test');
        Route::get('/analytics', [SuperAdminController::class, 'analytics'])->name('analytics');
        Route::get('/adherence-testing', [SuperAdminAdherenceTestController::class, 'index'])->name('adherence-tests.index');
        Route::post('/adherence-testing', [SuperAdminAdherenceTestController::class, 'send'])->name('adherence-tests.send');
        Route::get('/backup', [DatabaseBackupController::class, 'index'])->name('backup');
        Route::post('/backup', [DatabaseBackupController::class, 'download'])->name('backup.download');
        Route::put('/backup/connections', [SuperAdminConnectionSettingsController::class, 'update'])->name('backup.connections.update');
        Route::post('/backup/test-database', [SuperAdminConnectionSettingsController::class, 'testDatabase'])->name('backup.connections.test-database');
        Route::post('/backup/test-ollama', [SuperAdminConnectionSettingsController::class, 'testOllama'])->name('backup.connections.test-ollama');
    });

    // Adherence Routes (Accessible to Pet Owners and Staff)
    Route::middleware(['role:owner,staff', 'pet-owner.verified'])->prefix('adherence')->name('adherence.')->group(function () {
        Route::get('/dashboard', [AdherenceController::class, 'dashboard'])->name('dashboard');
        Route::post('/reminder', [AdherenceController::class, 'createReminder'])->name('create-reminder');
        Route::post('/confirm/{notificationId}', [AdherenceController::class, 'confirmAdherence'])->name('confirm');
        Route::post('/doses/{adherenceLog}/confirm', [AdherenceController::class, 'confirmDose'])->name('confirm-dose');
        Route::post('/snooze/{notificationId}', [AdherenceController::class, 'snoozeReminder'])->name('snooze');
        Route::delete('/notification/{notificationId}', [AdherenceController::class, 'deleteNotification'])->name('delete-notification');
        Route::get('/pending-count', [AdherenceController::class, 'getPendingCount'])->name('pending-count');
        Route::post('/mark-expired', [AdherenceController::class, 'markExpiredAsMissed'])->name('mark-expired');
        Route::get('/prescription/{prescriptionId}/history', [AdherenceController::class, 'prescriptionHistory'])->name('prescription-history');
    });
});
