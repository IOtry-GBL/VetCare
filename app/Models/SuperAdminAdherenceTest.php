<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SuperAdminAdherenceTest extends Model
{
    protected $table = 'super_admin_adherence_tests';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'recipient_user_id',
        'created_by',
        'recipient_name',
        'recipient_email',
        'medication_name',
        'dosage',
        'token_hash',
        'status',
        'expires_at',
        'confirmed_at',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'confirmed_at' => 'datetime',
        ];
    }

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_user_id', 'user_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'user_id');
    }
}
