<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    protected $table = 'reminder_logs';
    protected $primaryKey = 'reminder_id';
    protected $fillable = ['user_id', 'reminder_type', 'reminder_channel', 'scheduled_at', 'sent_status', 'related_reference'];

    protected $casts = [
        'scheduled_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
