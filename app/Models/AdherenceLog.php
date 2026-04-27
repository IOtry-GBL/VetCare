<?php

namespace App\Models;

use App\Services\AdherenceService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class AdherenceLog extends Model
{
    protected $primaryKey = 'adherence_id';
    protected $fillable = [
        'prescription_id',
        'scheduled_datetime',
        'confirmation_deadline',
        'intake_status',
        'confirmation_time',
        'remarks',
        'is_notified',
    ];

    protected $casts = [
        'scheduled_datetime' => 'datetime',
        'confirmation_deadline' => 'datetime',
        'confirmation_time' => 'datetime',
    ];

    public function prescription(): BelongsTo
    {
        return $this->belongsTo(EPrescription::class, 'prescription_id');
    }

    public function notification(): HasOne
    {
        return $this->hasOne(AdherenceNotification::class, 'adherence_id');
    }

    public function resolvedConfirmationDeadline(): ?Carbon
    {
        if ($this->confirmation_deadline instanceof Carbon) {
            return $this->confirmation_deadline->copy();
        }

        if ($this->scheduled_datetime instanceof Carbon) {
            return $this->scheduled_datetime->copy()->addHours(AdherenceService::confirmationWindowHours());
        }

        return null;
    }

    public function scheduledDatetimeInClinicTimezone(): ?Carbon
    {
        return $this->scheduled_datetime?->copy()->timezone(AdherenceService::clinicTimezone());
    }

    public function confirmationDeadlineInClinicTimezone(): ?Carbon
    {
        return $this->resolvedConfirmationDeadline()?->timezone(AdherenceService::clinicTimezone());
    }

    public function isUpcoming(): bool
    {
        return $this->intake_status === 'Pending'
            && $this->scheduled_datetime instanceof Carbon
            && now()->lt($this->scheduled_datetime);
    }

    public function isExpiredForConfirmation(): bool
    {
        $deadline = $this->resolvedConfirmationDeadline();

        return $this->intake_status === 'Pending'
            && $deadline instanceof Carbon
            && now()->gt($deadline);
    }

    public function isAvailableForConfirmation(): bool
    {
        $deadline = $this->resolvedConfirmationDeadline();

        return $this->intake_status === 'Pending'
            && $this->scheduled_datetime instanceof Carbon
            && $deadline instanceof Carbon
            && now()->greaterThanOrEqualTo($this->scheduled_datetime)
            && now()->lessThanOrEqualTo($deadline);
    }
}
