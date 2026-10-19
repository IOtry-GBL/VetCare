<?php

namespace App\Http\Controllers;

use App\Models\SuperAdminConnectionSetting;
use App\Services\SystemMailSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class SuperAdminMailSettingsController extends Controller
{
    public function update(Request $request, SystemMailSettings $mailSettings)
    {
        $validated = $request->validate([
            'mail_enabled' => ['nullable', 'boolean'],
            'mail_host' => ['required_if:mail_enabled,1', 'nullable', 'string', 'not_regex:/^\s*$/', 'max:255'],
            'mail_port' => ['required_if:mail_enabled,1', 'nullable', 'integer', 'between:1,65535'],
            'mail_encryption' => ['required', Rule::in(['tls', 'ssl', 'none'])],
            'mail_username' => ['nullable', 'string', 'max:255'],
            'mail_password' => ['nullable', 'string', 'max:4096'],
            'clear_mail_password' => ['nullable', 'boolean'],
            'mail_from_address' => ['required_if:mail_enabled,1', 'nullable', 'email', 'max:255'],
            'mail_from_name' => ['required_if:mail_enabled,1', 'nullable', 'string', 'max:255'],
        ]);

        $settings = $this->settingsRecord($request);
        $settings->fill([
            'mail_enabled' => $request->boolean('mail_enabled'),
            'mail_host' => isset($validated['mail_host']) ? trim($validated['mail_host']) : null,
            'mail_port' => $validated['mail_port'] ?? null,
            'mail_encryption' => $validated['mail_encryption'],
            'mail_username' => isset($validated['mail_username']) ? trim($validated['mail_username']) : null,
            'mail_from_address' => isset($validated['mail_from_address']) ? trim($validated['mail_from_address']) : null,
            'mail_from_name' => isset($validated['mail_from_name']) ? trim($validated['mail_from_name']) : null,
            'updated_by' => $request->user()->user_id,
        ]);

        if ($request->boolean('clear_mail_password')) {
            $settings->mail_password = null;
        } elseif (! empty($validated['mail_password'])) {
            $settings->mail_password = $validated['mail_password'];
        }

        $settings->save();
        $mailSettings->applySaved();
        Mail::purge('smtp');

        return redirect()->route('super-admin.settings')
            ->with('success', 'Email settings saved. '.($settings->mail_enabled ? 'SMTP email is enabled.' : 'SMTP email is disabled.'));
    }

    public function test(Request $request, SystemMailSettings $mailSettings)
    {
        $validated = $request->validate([
            'mail_host' => ['required', 'string', 'not_regex:/^\s*$/', 'max:255'],
            'mail_port' => ['required', 'integer', 'between:1,65535'],
            'mail_encryption' => ['required', Rule::in(['tls', 'ssl', 'none'])],
            'mail_username' => ['nullable', 'string', 'max:255'],
            'mail_password' => ['nullable', 'string', 'max:4096'],
            'clear_mail_password' => ['nullable', 'boolean'],
            'mail_from_address' => ['required', 'email', 'max:255'],
            'mail_from_name' => ['required', 'string', 'max:255'],
            'test_email' => ['required', 'email', 'max:255'],
        ]);

        $settings = $this->settingsRecord($request);
        $settings->fill([
            'mail_host' => trim($validated['mail_host']),
            'mail_port' => $validated['mail_port'],
            'mail_encryption' => $validated['mail_encryption'],
            'mail_username' => isset($validated['mail_username']) ? trim($validated['mail_username']) : null,
            'mail_from_address' => trim($validated['mail_from_address']),
            'mail_from_name' => trim($validated['mail_from_name']),
        ]);

        if ($request->boolean('clear_mail_password')) {
            $settings->mail_password = null;
        } elseif (! empty($validated['mail_password'])) {
            $settings->mail_password = $validated['mail_password'];
        } elseif (! $settings->mail_password) {
            $settings->mail_password = config('mail.mailers.smtp.password');
        }

        $mailSettings->apply($settings);
        Mail::purge('smtp');

        try {
            Mail::mailer('smtp')->raw(
                'This is a test email from '.config('app.name').'. Your SMTP settings are working.',
                function ($message) use ($validated): void {
                    $message->to($validated['test_email'])
                        ->subject('SMTP settings test');
                }
            );
        } catch (TransportExceptionInterface|RuntimeException $exception) {
            Log::warning('Super Admin SMTP test email failed.', [
                'host' => $settings->mail_host,
                'recipient' => $validated['test_email'],
                'exception' => $exception::class,
            ]);

            return redirect()->route('super-admin.settings')
                ->with('mail_test_error', 'The test email could not be sent. Check the SMTP host, port, encryption, username, password, and sender address.');
        }

        return redirect()->route('super-admin.settings')
            ->with('mail_test_success', 'Test email sent successfully to '.$validated['test_email'].'.');
    }

    private function settingsRecord(Request $request): SuperAdminConnectionSetting
    {
        $database = config('database.connections.mysql');

        $settings = SuperAdminConnectionSetting::query()->firstOrNew(['id' => 1]);

        if (! $settings->exists) {
            $settings->id = 1;
            $settings->fill([
                'database_driver' => 'mysql',
                'database_host' => $database['host'] ?? '127.0.0.1',
                'database_port' => $database['port'] ?? 3306,
                'database_name' => $database['database'] ?? 'laravel',
                'database_username' => $database['username'] ?? '',
                'updated_by' => $request->user()->user_id,
            ]);
        }

        return $settings;
    }
}
