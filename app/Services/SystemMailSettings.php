<?php

namespace App\Services;

use App\Models\SuperAdminConnectionSetting;
use Illuminate\Support\Facades\Schema;

class SystemMailSettings
{
    public function __construct(
        private readonly string $defaultMailer,
        private readonly array $smtpMailer,
        private readonly array $from
    ) {
    }

    public function applySaved(): void
    {
        if (! Schema::hasTable('super_admin_connection_settings')) {
            $this->restoreDefaults();

            return;
        }

        $settings = SuperAdminConnectionSetting::query()->find(1);
        if (! $settings?->mail_enabled) {
            $this->restoreDefaults();

            return;
        }

        $this->apply($settings);
    }

    public function apply(SuperAdminConnectionSetting $settings): void
    {
        $encryption = $settings->mail_encryption ?: 'tls';

        config([
            'mail.default' => 'smtp',
            'mail.mailers.smtp' => [
                'transport' => 'smtp',
                'scheme' => $encryption === 'ssl' ? 'smtps' : 'smtp',
                'host' => $settings->mail_host,
                'port' => $settings->mail_port,
                'username' => $settings->mail_username ?: null,
                'password' => $settings->mail_password ?: null,
                'timeout' => 15,
                'local_domain' => parse_url((string) config('app.url'), PHP_URL_HOST),
                'auto_tls' => $encryption !== 'none',
                'require_tls' => $encryption === 'tls',
            ],
            'mail.from.address' => $settings->mail_from_address,
            'mail.from.name' => $settings->mail_from_name,
        ]);
    }

    private function restoreDefaults(): void
    {
        config([
            'mail.default' => $this->defaultMailer,
            'mail.mailers.smtp' => $this->smtpMailer,
            'mail.from' => $this->from,
        ]);
    }
}
