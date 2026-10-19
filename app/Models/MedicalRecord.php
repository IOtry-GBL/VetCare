<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MedicalRecord extends Model
{
    use HasFactory;

    protected $primaryKey = 'record_id';
    protected $fillable = [
        'pet_id',
        'consultation_id',
        'diagnosis',
        'treatment_plan',
        'vaccination_notes',
        'follow_up_date',
        'weight',
        'temp',
        'food',
        'environment',
        'fee',
    ];
    protected $casts = [
        'follow_up_date' => 'date',
        'weight' => 'decimal:2',
        'temp' => 'decimal:2',
        'fee' => 'decimal:2',
    ];

    public function pet(): BelongsTo
    {
        return $this->belongsTo(Pet::class, 'pet_id');
    }

    public function consultation(): BelongsTo
    {
        return $this->belongsTo(Consultation::class, 'consultation_id');
    }

    public function prescriptions(): HasMany
    {
        return $this->hasMany(EPrescription::class, 'record_id');
    }
}
