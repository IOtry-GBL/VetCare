<?php

namespace App\Http\Controllers;

use App\Models\SuperAdminConnectionSetting;
use App\Services\ConnectionSettingsTester;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class SuperAdminConnectionSettingsController extends Controller
{
    public function update(Request $request)
    {
        $validated = $request->validate([
            'database_driver' => ['required', Rule::in(['mysql', 'mariadb'])],
            'database_host' => ['required', 'string', 'max:255'],
            'database_port' => ['required', 'integer', 'between:1,65535'],
            'database_name' => ['required', 'string', 'max:255'],
            'database_username' => ['required', 'string', 'max:255'],
            'database_password' => ['nullable', 'string', 'max:4096'],
            'clear_database_password' => ['nullable', 'boolean'],
            'ollama_base_url' => ['nullable', 'url', 'max:2048'],
            'ollama_api_key' => ['nullable', 'string', 'max:4096'],
            'clear_ollama_api_key' => ['nullable', 'boolean'],
            'ollama_model' => ['nullable', 'string', 'max:255'],
        ]);

        foreach (['ollama_base_url'] as $urlKey) {
            if (! empty($validated[$urlKey]) && ! in_array(parse_url($validated[$urlKey], PHP_URL_SCHEME), ['http', 'https'], true)) {
                throw ValidationException::withMessages([
                    $urlKey => 'The Ollama API URL must use HTTP or HTTPS.',
                ]);
            }
            if (! empty($validated[$urlKey]) && (parse_url($validated[$urlKey], PHP_URL_USER) !== null || parse_url($validated[$urlKey], PHP_URL_PASS) !== null)) {
                throw ValidationException::withMessages([
                    $urlKey => 'Do not include credentials in the Ollama API URL; use the API key field instead.',
                ]);
            }
        }

        $settings = SuperAdminConnectionSetting::query()->firstOrNew(['id' => 1]);
        $settings->fill([
            'database_driver' => $validated['database_driver'],
            'database_host' => trim($validated['database_host']),
            'database_port' => $validated['database_port'],
            'database_name' => trim($validated['database_name']),
            'database_username' => trim($validated['database_username']),
            'ollama_base_url' => isset($validated['ollama_base_url'])
                ? rtrim($validated['ollama_base_url'], '/')
                : null,
            'ollama_model' => isset($validated['ollama_model']) ? trim($validated['ollama_model']) : null,
            'updated_by' => $request->user()->user_id,
        ]);

        if (! empty($validated['clear_database_password'])) {
            $settings->database_password = null;
        } elseif (! empty($validated['database_password'])) {
            $settings->database_password = $validated['database_password'];
        }

        if (! empty($validated['clear_ollama_api_key'])) {
            $settings->ollama_api_key = null;
        } elseif (! empty($validated['ollama_api_key'])) {
            $settings->ollama_api_key = $validated['ollama_api_key'];
        }

        $settings->save();

        return redirect()->route('super-admin.backup')
            ->with('success', 'Connection settings saved. They do not change the live application database connection.');
    }

    public function testDatabase(Request $request, ConnectionSettingsTester $tester)
    {
        $settings = $this->databaseSettingsFromRequest($request);

        try {
            $tester->testDatabase($settings);
        } catch (RuntimeException $exception) {
            Log::warning('Super Admin database connection test failed.', [
                'driver' => $settings->database_driver,
                'host' => $settings->database_host,
                'database' => $settings->database_name,
                'error' => $exception->getMessage(),
            ]);

            return redirect()->route('super-admin.backup')
                ->with('error', 'Could not connect to the configured SQL server. Check the host, port, database name, username, password, and server access rules.');
        }

        return redirect()->route('super-admin.backup')
            ->with('connection_status', 'Connection Successful');
    }

    public function testOllama(Request $request, ConnectionSettingsTester $tester)
    {
        $settings = $this->ollamaSettingsFromRequest($request);

        try {
            $tester->testOllama($settings);
        } catch (ConnectionException|RuntimeException $exception) {
            Log::warning('Super Admin Ollama connection test failed.', [
                'host' => parse_url((string) $settings->ollama_base_url, PHP_URL_HOST),
                'error' => $exception->getMessage(),
            ]);

            return redirect()->route('super-admin.backup')
                ->with('error', 'Could not connect to Ollama. Check the API URL, server availability, and credentials.');
        }

        return redirect()->route('super-admin.backup')
            ->with('connection_status', 'Connection Successful');
    }

    private function databaseSettingsFromRequest(Request $request): SuperAdminConnectionSetting
    {
        if (! $request->exists('database_host')) {
            return $this->savedSettings();
        }

        $validated = $request->validate([
            'database_driver' => ['required', Rule::in(['mysql', 'mariadb'])],
            'database_host' => ['required', 'string', 'max:255'],
            'database_port' => ['required', 'integer', 'between:1,65535'],
            'database_name' => ['required', 'string', 'max:255'],
            'database_username' => ['required', 'string', 'max:255'],
            'database_password' => ['nullable', 'string', 'max:4096'],
        ]);

        $savedSettings = SuperAdminConnectionSetting::query()->find(1);
        $settings = new SuperAdminConnectionSetting([
            ...$validated,
            'database_host' => trim($validated['database_host']),
            'database_name' => trim($validated['database_name']),
            'database_username' => trim($validated['database_username']),
            'database_password' => $request->boolean('clear_database_password')
                ? null
                : ($validated['database_password'] ?: $savedSettings?->database_password),
        ]);

        return $settings;
    }

    private function ollamaSettingsFromRequest(Request $request): SuperAdminConnectionSetting
    {
        if (! $request->exists('ollama_base_url')) {
            return $this->savedSettings();
        }

        $validated = $request->validate([
            'ollama_base_url' => ['required', 'url', 'max:2048'],
            'ollama_api_key' => ['nullable', 'string', 'max:4096'],
        ]);

        if (! in_array(parse_url($validated['ollama_base_url'], PHP_URL_SCHEME), ['http', 'https'], true)) {
            throw ValidationException::withMessages([
                'ollama_base_url' => 'The Ollama API URL must use HTTP or HTTPS.',
            ]);
        }
        if (parse_url($validated['ollama_base_url'], PHP_URL_USER) !== null || parse_url($validated['ollama_base_url'], PHP_URL_PASS) !== null) {
            throw ValidationException::withMessages([
                'ollama_base_url' => 'Do not include credentials in the Ollama API URL; use the API key field instead.',
            ]);
        }

        $savedSettings = SuperAdminConnectionSetting::query()->find(1);

        return new SuperAdminConnectionSetting([
            'ollama_base_url' => rtrim($validated['ollama_base_url'], '/'),
            'ollama_api_key' => $request->boolean('clear_ollama_api_key')
                ? null
                : ($validated['ollama_api_key'] ?: $savedSettings?->ollama_api_key),
        ]);
    }

    private function savedSettings(): SuperAdminConnectionSetting
    {
        $settings = SuperAdminConnectionSetting::query()->find(1);
        if (! $settings) {
            throw ValidationException::withMessages([
                'connection_settings' => 'Save the connection settings before testing.',
            ]);
        }

        return $settings;
    }
}
