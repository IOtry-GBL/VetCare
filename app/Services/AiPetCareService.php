<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class AiPetCareService
{
    public function getAvailableSymptoms(): array
    {
        $url = rtrim((string) config('services.ai_pet_care.url'), '/');

        return Http::acceptJson()
            ->timeout(5)
            ->get($url.'/symptoms')
            ->throw()
            ->json('symptoms', []);
    }

    public function getGuidance(array $petData): array
    {
        $url = rtrim((string) config('services.ai_pet_care.url'), '/');

        return Http::acceptJson()
            ->timeout(30)
            ->post($url.'/guidance', $petData)
            ->throw()
            ->json();
    }
}