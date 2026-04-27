<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Consultation extends Model
{
    use HasFactory;

    protected $primaryKey = 'consultation_id';

    protected $fillable = [
        'appointment_id',
        'veterinarian_id',
        'chief_complaint',
        'ai_guidance_summary',
        'consultation_notes',
        'consultation_date',
        'status',
    ];

    protected $casts = [
        'consultation_date' => 'datetime',
    ];

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class, 'appointment_id', 'appointment_id');
    }

    public function veterinarian(): BelongsTo
    {
        return $this->belongsTo(User::class, 'veterinarian_id', 'user_id');
    }

    public function medicalRecord(): HasOne
    {
        return $this->hasOne(MedicalRecord::class, 'consultation_id', 'consultation_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ConsultationMessage::class, 'consultation_id', 'consultation_id');
    }
}
