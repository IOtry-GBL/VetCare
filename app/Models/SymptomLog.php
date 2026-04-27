<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SymptomLog extends Model
{
    protected $fillable = [
        'pet_id', 'itching', 'hair_loss', 'redness', 'wounds',
        'fever', 'vomiting', 'diarrhea', 'duration_days',
        'ai_prediction', 'concern_level'
    ];

    protected $casts = [
        'itching' => 'boolean',
        'hair_loss' => 'boolean',
        'redness' => 'boolean',
        'wounds' => 'boolean',
        'fever' => 'boolean',
        'vomiting' => 'boolean',
        'diarrhea' => 'boolean',
    ];

    public function pet(): BelongsTo
    {
        return $this->belongsTo(Pet::class, 'pet_id');
    }

    public function isEmergency(): bool
    {
        return $this->concern_level === 'emergency';
    }

    public function needsVetVisit(): bool
    {
        return in_array($this->concern_level, ['emergency', 'vet_visit']);
    }
}
