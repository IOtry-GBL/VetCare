<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SuperAdminConnectionSetting extends Model
{
    protected $table = 'super_admin_connection_settings';

    public $incrementing = false;

    protected $fillable = [
        'database_driver',
        'database_host',
        'database_port',
        'database_name',
        'database_username',
        'database_password',
        'ollama_base_url',
        'ollama_api_key',
        'ollama_model',
        'mail_enabled',
        'mail_host',
        'mail_port',
        'mail_encryption',
        'mail_username',
        'mail_password',
        'mail_from_address',
        'mail_from_name',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'database_password' => 'encrypted',
            'ollama_api_key' => 'encrypted',
            'database_port' => 'integer',
            'mail_enabled' => 'boolean',
            'mail_port' => 'integer',
            'mail_password' => 'encrypted',
        ];
    }
}
