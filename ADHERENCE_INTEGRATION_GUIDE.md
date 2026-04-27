# Adherence System Integration Guide

## How to Integrate with Existing Prescription System

### 1. Add to Prescription Creation

When creating a new prescription, you can automatically generate adherence reminders:

```php
// In your prescription controller
use App\Services\AdherenceService;

// After creating prescription
$prescription = EPrescription::create([
    'record_id' => $recordId,
    'medication_name' => $request->medication_name,
    'dosage' => $request->dosage,
    'frequency' => $request->frequency,
    'duration' => $request->duration,
    'issued_at' => now(),
]);

// Create dose schedule (example: daily for 10 days)
$scheduleTimes = [];
for ($i = 0; $i < 10; $i++) {
    $scheduleTimes[] = now()->addDays($i)->setTime(9, 0); // 9 AM daily
}

// Create reminders
$notifications = AdherenceService::createRemindersForPrescription($prescription, $scheduleTimes);
```

### 2. Manual Reminder Creation

If you have existing adherence logs and want to create reminders:

```php
use App\Services\AdherenceService;

$adherenceLog = AdherenceLog::find($logId);
$notification = AdherenceService::createReminderForDose($adherenceLog);
```

### 3. Get User Adherence Statistics

Display user's adherence performance:

```php
use App\Services\AdherenceService;

$stats = AdherenceService::getUserAdherenceStats($userId, 30);
// Returns: ['total' => 30, 'confirmed' => 25, 'missed' => 2, 'pending' => 3, 'confirmation_rate' => 83.33]
```

### 4. Get Pet Adherence Statistics

Track a specific pet's medication adherence:

```php
use App\Services\AdherenceService;

$stats = AdherenceService::getPetAdherenceStats($petId, 30);
// Returns pet-specific adherence data
```

### 5. Create Prescription Report

Generate adherence report for veterinarian review:

```php
use App\Services\AdherenceService;

$report = AdherenceService::getPrescriptionAdherenceReport($prescriptionId);
// Shows detailed adherence for the medication
```

### 6. Get Upcoming Medications

Show user their next 24 hours of medications:

```php
use App\Services\AdherenceService;

$upcoming = AdherenceService::getUpcomingMedications($userId, 24);
// Returns notifications scheduled for next 24 hours
```

## Integration Examples

### Example 1: Dashboard Widget

```php
// PetOwnerController.php
public function dashboard()
{
    $user = auth()->user();
    
    // Get adherence stats
    $stats = AdherenceService::getUserAdherenceStats($user->user_id, 30);
    
    // Get upcoming medications
    $upcoming = AdherenceService::getUpcomingMedications($user->user_id, 24);
    
    return view('pet-owner.dashboard', [
        'adherenceStats' => $stats,
        'upcomingMedications' => $upcoming,
    ]);
}
```

### Example 2: Veterinarian Adherence Monitoring

```php
// VeterinarianController.php
public function adherenceMonitoring()
{
    $prescriptions = EPrescription::where('veterinarian_id', auth()->id())->get();
    
    $prescriptionAdherence = $prescriptions->map(function ($prescription) {
        return AdherenceService::getPrescriptionAdherenceReport($prescription->prescription_id);
    });
    
    return view('vet.adherence-monitoring', [
        'prescriptions' => $prescriptionAdherence,
    ]);
}
```

### Example 3: Automated Cleanup

```php
// app/Console/Commands/CleanupAdherence.php
public function handle()
{
    // Mark expired as missed
    $marked = AdherenceService::markExpiredAsMissed();
    $this->info("Marked {$marked} as missed");
    
    // Clean old deleted records
    $deleted = AdherenceService::cleanupDeletedNotifications(90);
    $this->info("Deleted {$deleted} old records");
}
```

### Example 4: Appointment Session Integration

```php
// When completing appointment session
public function completeAppointment($appointmentId)
{
    $appointment = Appointment::find($appointmentId);
    
    // Create prescription
    $prescription = EPrescription::create([...]);
    
    // Auto-create adherence schedule
    $doses = $this->generateDoseSchedule($prescription);
    AdherenceService::createRemindersForPrescription($prescription, $doses);
    
    return redirect()->with('success', 'Prescription created with adherence reminders');
}

private function generateDoseSchedule($prescription)
{
    $doses = [];
    $start = now()->addDay();
    $daysPerDose = $this->getDaysPerDose($prescription->frequency);
    $durationDays = $this->parseDuration($prescription->duration);
    
    for ($i = 0; $i < $durationDays; $i++) {
        $doses[] = $start->copy()->addDays($i)->setTime(9, 0);
    }
    
    return $doses;
}
```

## Livewire Component Usage

### Show Reminders in Any View

```blade
<!-- In any blade template -->
@livewire('adherence-reminder')
@livewire('adherence-notification-inbox')
```

### Trigger Actions from Livewire

```php
// In a Livewire component
public function createMedicationReminder($adherenceId)
{
    $adherenceLog = AdherenceLog::find($adherenceId);
    AdherenceService::createReminderForDose($adherenceLog);
    
    $this->dispatch('reminderCreated', ['id' => $adherenceId]);
}
```

## Database Queries

### Find All Pending Reminders

```php
$pending = AdherenceNotification::where('status', 'Pending')
    ->where('confirmation_deadline', '>', now())
    ->with('user', 'adherenceLog')
    ->get();
```

### Get User's Missed Doses This Month

```php
$missed = AdherenceNotification::where('user_id', $userId)
    ->where('status', 'Missed')
    ->whereMonth('scheduled_at', now()->month)
    ->whereYear('scheduled_at', now()->year)
    ->count();
```

### Get Adherence Trend

```php
$adherenceByDay = AdherenceNotification::where('user_id', $userId)
    ->where('status', '!=', 'Deleted')
    ->selectRaw('DATE(scheduled_at) as date, COUNT(*) as total, SUM(CASE WHEN status = "Confirmed" THEN 1 ELSE 0 END) as confirmed')
    ->groupBy('date')
    ->orderBy('date', 'desc')
    ->limit(30)
    ->get();
```

## API Response Examples

### Create Reminder
```json
{
  "message": "Reminder created successfully",
  "notification": {
    "notification_id": 1,
    "user_id": 5,
    "medication_name": "Amoxicillin",
    "dosage": "500mg",
    "scheduled_at": "2026-04-18T09:00:00",
    "confirmation_deadline": "2026-04-18T10:00:00",
    "status": "Pending"
  }
}
```

### Confirm Adherence
```json
{
  "message": "Adherence confirmed successfully",
  "notification": {
    "notification_id": 1,
    "status": "Confirmed",
    "confirmed_at": "2026-04-18T09:15:00",
    "notes": "Pet took medication with food"
  }
}
```

### Get Adherence Stats
```json
{
  "total": 30,
  "confirmed": 25,
  "missed": 2,
  "pending": 3,
  "confirmation_rate": 83.33,
  "period_days": 30
}
```

## Scheduling

Add to `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule)
{
    // Mark expired reminders as missed every minute
    $schedule->command('adherence:mark-expired')->everyMinute();
    
    // Clean up old deleted notifications daily
    $schedule->command('adherence:cleanup')->daily();
}
```

Or create the cleanup command:

```php
<?php
namespace App\Console\Commands;

use App\Services\AdherenceService;
use Illuminate\Console\Command;

class CleanupAdherence extends Command
{
    protected $signature = 'adherence:cleanup';
    protected $description = 'Clean up old deleted adherence notifications';

    public function handle()
    {
        $count = AdherenceService::cleanupDeletedNotifications(90);
        $this->info("Cleaned up {$count} old notifications");
        return Command::SUCCESS;
    }
}
```

## Troubleshooting Integration

### Issue: Reminders not showing up
- Verify migrations ran: `php artisan migrate:status`
- Check if `adherence_notifications` table exists: `php artisan tinker` → `DB::table('adherence_notifications')->count()`
- Ensure Livewire scripts are loaded in layout

### Issue: Confirmation not working
- Check user authorization in middleware
- Verify notification status is 'Pending'
- Ensure deadline hasn't passed

### Issue: Statistics showing zeros
- Verify adherence_logs have associated notifications
- Check date ranges in queries
- Run: `AdherenceService::getUserAdherenceStats($userId)` in tinker

## Performance Optimization

### Add Indexes
```sql
CREATE INDEX idx_adherence_notifications_user_status 
ON adherence_notifications(user_id, status);

CREATE INDEX idx_adherence_notifications_deadline 
ON adherence_notifications(confirmation_deadline);
```

### Cache Statistics
```php
$stats = cache()->remember(
    "user_{$userId}_adherence_stats",
    3600, // 1 hour
    fn() => AdherenceService::getUserAdherenceStats($userId)
);
```

### Batch Operations
```php
AdherenceNotification::where('status', 'Pending')
    ->where('confirmation_deadline', '<=', now())
    ->update(['status' => 'Missed']);
```
