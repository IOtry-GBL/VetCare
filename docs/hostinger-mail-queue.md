# Hostinger email queue setup

Adherence reminder emails are placed on Laravel's database queue with an `available_at` timestamp equal to the dose's scheduled time, when the confirmation window opens. Appointment reminder emails (one day before, one hour before, and at the scheduled time) use the same `emails` queue. The adherence job cannot be processed before its scheduled timestamp, keeping SMTP network delays out of appointment requests.

After deploying code and configuring SMTP in Super Admin settings:

1. Run database migrations from the Laravel application directory:

   ```sh
   php artisan migrate --force
   ```

2. For local development, keep a queue worker running in a separate terminal:

   ```sh
   php artisan queue:work database --queue=emails --sleep=1 --timeout=45 --tries=3
   ```

   Reminder jobs are delayed until each dose's scheduled time, so starting this worker does not send future reminders immediately. The Super Admin SMTP test verifies email transport only; it does not process queued reminders.

3. In Hostinger hPanel, add a cron task that runs every minute:

   ```sh
   cd /home/ACCOUNT/domains/DOMAIN/public_html && php artisan schedule:run >> /dev/null 2>&1
   ```

   Replace the directory with the deployed Laravel project path. If `php` is not on the cron `PATH`, use the PHP executable path shown by Hostinger.

The Laravel scheduler starts a short-lived worker for the `emails` queue every minute. Hostinger's once-per-minute cron means a due email is sent on the first worker run after the scheduled time (usually within a minute), not necessarily at the exact second. For delivery as close to the exact opening time as possible, keep a long-running worker active instead:

```sh
php artisan queue:work database --queue=emails --sleep=1 --timeout=45 --tries=3
```

Use a process manager to keep that worker running where Hostinger supports it. Do not run both the persistent worker and the scheduled worker unless you intentionally want redundant workers; multiple workers can safely process the same queue but are unnecessary. If SMTP delivery repeatedly fails, Laravel records the job in `failed_jobs`; inspect it with `php artisan queue:failed`.
