<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EPrescription extends Model
{
    protected $table = 'e_prescriptions';
    protected $primaryKey = 'prescription_id';
    protected $fillable = ['record_id', 'medication_name', 'dosage', 'frequency', 'duration', 'issued_at'];

    protected $casts = [
        'issued_at' => 'datetime',
    ];

    public $timestamps = false;

    public function medicalRecord(): BelongsTo
    {
        return $this->belongsTo(MedicalRecord::class, 'record_id');
    }

    public function adherenceLogs(): HasMany
    {
        return $this->hasMany(AdherenceLog::class, 'prescription_id');
    }
}
