<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConsultationMessage extends Model
{
    use HasFactory;

    protected $primaryKey = 'message_id';

    protected $fillable = [
        'consultation_id',
        'sender_id',
        'message_body',
    ];

    public function consultation(): BelongsTo
    {
        return $this->belongsTo(Consultation::class, 'consultation_id', 'consultation_id');
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id', 'user_id');
    }
}
