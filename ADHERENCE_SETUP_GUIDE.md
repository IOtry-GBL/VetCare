# Medication Adherence System - Setup Guide

## Overview
This system provides real-time medication adherence tracking with pop-up reminders, 1-hour confirmation windows, and a notification inbox for pet owners to track their medication adherence for their pets.

## Components Installed

### 1. Database Tables
- **adherence_logs** - Enhanced with:
  - `confirmation_deadline` - When confirmation window expires
  - `is_notified` - Whether notification was sent
  
- **adherence_notifications** - New table storing:
  - User and adherence relationships
  - Medication details
  - Scheduled time and deadline
  - Status (Pending, Confirmed, Missed, Deleted)
  - Confirmation timestamp

### 2. Models
- `AdherenceNotification` - Manages notification lifecycle
- `AdherenceLog` - Updated with notification relationship

### 3. Livewire Components
- `AdherenceReminder` - Pop-up modal for confirmations
- `AdherenceNotificationInbox` - Inbox with filtering and management

### 4. Controller
- `AdherenceController` - Handles all adherence operations

### 5. Routes
All routes under `/adherence/` prefix

### 6. Views
- Adherence dashboard for pet owners
- Prescription history view
- Livewire component views

## Setup Instructions

### Step 1: Run Migrations
```bash
php artisan migrate
```

This will create the new tables and columns needed for the system.

### Step 2: Schedule the Cleanup Command
Edit `app/Console/Kernel.php` and add to the `schedule()` method:

```php
$schedule->command('adherence:mark-expired')->everyMinute();
```

Or if using Laravel 11+, create `routes/console.php` entry:
```php
Schedule::command('adherence:mark-expired')->everyMinute();
```

### Step 3: Add to Pet Owner Dashboard
Include the components in your pet owner dashboard layout:

```blade
@livewire('adherence-reminder')
@livewire('adherence-notification-inbox')
```

## How It Works

### Creating a Reminder
1. Veterinarian prescribes medication for a pet
2. System creates an `AdherenceLog` entry with scheduled time
3. Pet owner receives pop-up reminder at scheduled time

```php
// Via API
POST /adherence/reminder
{
    "adherence_id": 1
}
```

### Confirmation Workflow
1. **Pending State**: Pet owner sees pop-up reminder
2. **Confirmation Window**: 1 hour to confirm medication was taken
3. **Confirmed**: Status changes to "Taken" + timestamp recorded
4. **Expired**: Auto-marked as "Missed" after 1 hour

### User Actions

#### Confirm Medication Taken
```php
POST /adherence/confirm/{notificationId}
{
    "notes": "Optional notes about medication"
}
```

#### Snooze Reminder
```php
POST /adherence/snooze/{notificationId}
{
    "minutes": 15  // 5-120 minutes
}
```

#### Delete Notification
```php
DELETE /adherence/notification/{notificationId}
```

#### Check Pending Count
```php
GET /adherence/pending-count
// Returns: {"pending_count": 3}
```

## Database Schema

### adherence_notifications Table
```sql
CREATE TABLE adherence_notifications (
    notification_id BIGINT PRIMARY KEY,
    user_id BIGINT,
    adherence_id BIGINT,
    medication_name VARCHAR(255),
    dosage VARCHAR(255),
    scheduled_at TIMESTAMP,
    confirmation_deadline TIMESTAMP,
    status ENUM('Pending', 'Confirmed', 'Missed', 'Deleted'),
    confirmed_at TIMESTAMP NULL,
    notes TEXT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    FOREIGN KEY (adherence_id) REFERENCES adherence_logs(adherence_id),
    INDEX (user_id, status),
    INDEX (confirmation_deadline)
);
```

## API Endpoints

| Method | Endpoint | Purpose |
|--------|----------|---------|
| GET | `/adherence/dashboard` | View adherence dashboard |
| POST | `/adherence/reminder` | Create new reminder |
| POST | `/adherence/confirm/{id}` | Confirm medication taken |
| POST | `/adherence/snooze/{id}` | Snooze reminder |
| DELETE | `/adherence/notification/{id}` | Delete notification |
| GET | `/adherence/pending-count` | Get pending count |
| POST | `/adherence/mark-expired` | Mark expired as missed |
| GET | `/adherence/prescription/{id}/history` | View prescription history |

## Features

✅ **Real-time Pop-up Reminders** - Modal alerts for medication times
✅ **1-Hour Confirmation Window** - Configurable deadline
✅ **Auto-Miss Marking** - Automatically marks as missed if not confirmed
✅ **Notification Inbox** - Manage all notifications in one place
✅ **Tab Filtering** - View by status (All, Pending, Confirmed, Missed)
✅ **Delete Function** - Remove notifications from inbox
✅ **Expandable Details** - View full information about each medication
✅ **Statistics Dashboard** - See pending, missed, and confirmation rates
✅ **Prescription History** - Track adherence for each medication
✅ **Snooze Functionality** - Remind later (5-120 minutes)

## Customization

### Change Confirmation Window
Edit `AdherenceController.php` line where deadline is set:
```php
$deadlineTime = $scheduledTime->copy()->addHour(); // Change to addMinutes(90)
```

### Modify UI Appearance
Edit the Blade views:
- `resources/views/livewire/adherence-reminder.blade.php`
- `resources/views/livewire/adherence-notification-inbox.blade.php`

### Change Status Colors
Edit the Blade templates to modify color scheme based on status values.

## Troubleshooting

### Reminders Not Showing
1. Check migrations ran: `php artisan migrate:status`
2. Verify scheduler is running: `php artisan schedule:work`
3. Check `adherence_notifications` table has data

### Expired Notifications Not Marked
1. Ensure scheduler command is running every minute
2. Run manually: `php artisan adherence:mark-expired`
3. Check `confirmation_deadline` values in database

### Livewire Not Working
1. Verify Livewire installed: `composer show livewire/livewire`
2. Clear cache: `php artisan cache:clear`
3. Rebuild assets: `npm run build`

## Security

- All endpoints check user authorization
- Pet owners can only see their own notifications
- Staff can access all with `role:staff` middleware
- Delete operations are soft-deletes (status = 'Deleted')

## Future Enhancements

- SMS/Email notifications for missed doses
- Recurring medication schedules
- Adherence reports and analytics
- Integration with veterinarian dashboard
- Push notifications for mobile app
- Medication refill reminders
