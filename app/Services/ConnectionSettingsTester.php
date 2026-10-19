<?php

namespace App\Services;

use App\Models\SuperAdminConnectionSetting;
use Illuminate\Support\Facades\Http;
use PDO;
use RuntimeException;

class ConnectionSettingsTester
{
    public function testDatabase(SuperAdminConnectionSetting $settings): void
    {
        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
            $settings->database_host,
            $settings->database_port,
            $settings->database_name
        );

        new PDO($dsn, $settings->database_username, $settings->database_password ?? '', [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT => 5,
        ]);
    }

    /**
     * @return list<string>
     */
    public function testOllama(SuperAdminConnectionSetting $settings): array
    {
        $baseUrl = rtrim((string) $settings->ollama_base_url, '/');
        if ($baseUrl === '') {
            throw new RuntimeException('Save an Ollama API URL before testing the connection.');
        }

        $request = Http::acceptJson()->timeout(8);
        if ($settings->ollama_api_key) {
            $request = $request->withToken($settings->ollama_api_key);
        }

        $response = $request->get($baseUrl.'/api/tags');
        if (! $response->successful()) {
            throw new RuntimeException('Ollama returned HTTP '.$response->status().'. Check the API URL and credentials.');
        }

        $models = $response->json('models');
        if (! is_array($models)) {
            throw new RuntimeException('Ollama responded, but the model list was missing or invalid.');
        }

        return array_values(array_filter(array_map(
            static fn (mixed $model): ?string => is_array($model) && is_string($model['name'] ?? null)
                ? $model['name']
                : null,
            $models
        )));
    }
}
