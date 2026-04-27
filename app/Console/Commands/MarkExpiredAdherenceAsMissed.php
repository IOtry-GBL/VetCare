<?php

namespace App\Console\Commands;

use App\Models\AdherenceNotification;
use Illuminate\Console\Command;

class MarkExpiredAdherenceAsMissed extends Command
{
    protected $signature = 'adherence:mark-expired';
    protected $description = 'Mark expired adherence notifications as missed';

    public function handle()
    {
        $expiredNotifications = AdherenceNotification::where('status', 'Pending')
            ->where('confirmation_deadline', '<=', now())
            ->get();

        $count = $expiredNotifications->count();

        foreach ($expiredNotifications as $notification) {
            $notification->markAsMissed();
        }

        $this->info("Marked {$count} expired adherence notifications as missed.");
        return Command::SUCCESS;
    }
}
