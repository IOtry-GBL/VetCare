<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

class AiPetCareService
{
    private ?array $cachedBreedData = null;
    private ?array $cachedOverallData = null;

    private function normalize(string $value): string
    {
        return implode(' ', array_filter(explode(' ', trim(strtolower($value)))));
    }

    private function normalizeSymptom(string $value): string
    {
        $aliases = ['coughing' => 'cough', 'womiting' => 'vomiting'];
        $symptom = $this->normalize($value);
        return $aliases[$symptom] ?? $symptom;
    }

    private function symptomLabel(string $value): string
    {
        $normalized = $this->normalizeSymptom($value);
        $labels = ['cough' => 'Coughing', 'vomiting' => 'Vomiting'];
        return $labels[$normalized] ?? ucfirst($normalized);
    }

    private function readCsv(string $path): array
    {
        $rows = [];
        if (!file_exists($path)) {
            throw new ConnectionException('AI data file is unavailable.');
        }
        $handle = fopen($path, 'r');
        if ($handle === false) {
            throw new ConnectionException('AI data file is unavailable.');
        }
        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($handle);
        }
        $headers = fgetcsv($handle, 0, ',', '"', '');
        if ($headers === false) {
            fclose($handle);
            return [];
        }
        while (($data = fgetcsv($handle, 0, ',', '"', '')) !== false) {
            $row = [];
            for ($i = 0; $i < count($headers); $i++) {
                $key = $headers[$i] ?? $i;
                $row[$key] = $data[$i] ?? '';
            }
            $rows[] = $row;
        }
        fclose($handle);
        return $rows;
    }

    private function loadData(): array
    {
        $path = storage_path('app/private/ai-data/combined_pet_symptom_dataset.csv');
        $data = $this->readCsv($path);
        return [$data, $data];
    }

    public function getAvailableSymptoms(): array
    {
        [$breedRows] = $this->loadData();
        $labels = [];
        foreach ($breedRows as $row) {
            $normalizedSymptom = $this->normalizeSymptom($row['Symptoms'] ?? '');
            if ($normalizedSymptom !== '') {
                $labels[$normalizedSymptom] = true;
            }
        }
        $sorted = array_keys($labels);
        sort($sorted);
        return $sorted;
    }

    public function getGuidance(array $petData): array
    {
        $key = config('services.groq.key');
        $model = config('services.groq.model');
        if (empty($key) || empty($model)) {
            throw new ConnectionException('AI guidance is unavailable right now.');
        }

        [$breedRows] = $this->loadData();
        $species = $this->normalize($petData['species'] ?? '');
        $breed = $this->normalize($petData['breed'] ?? '');
        $requestedRaw = $petData['symptoms'] ?? [];
        $requested = [];
        foreach ($requestedRaw as $symptom) {
            $normalizedSymptom = $this->normalizeSymptom($symptom);
            if ($normalizedSymptom !== '') {
                $requested[] = $normalizedSymptom;
            }
        }
        $requested = array_values(array_unique($requested));

        $available = [];
        foreach ($breedRows as $row) {
            $symptomRaw = $row['Symptoms'] ?? '';
            $normalizedSymptom = $this->normalizeSymptom($symptomRaw);
            if ($normalizedSymptom !== '') {
                $available[$normalizedSymptom] = true;
            }
        }

        $unsupported = array_values(array_diff($requested, array_keys($available)));
        sort($unsupported);
        if (!empty($unsupported)) {
            throw new RequestException('Symptoms are not present in the datasets: ' . implode(', ', $unsupported));
        }

        $totalRows = count($breedRows);
        $breedTotal = 0;
        foreach ($breedRows as $row) {
            if (($this->normalize($row['Species'] ?? '') === $species) && ($this->normalize($row['Breed'] ?? '') === $breed)) {
                $breedTotal++;
            }
        }

        $frequencyRows = [];
        foreach ($requested as $symptom) {
            $breedSymptomCount = 0;
            $overallSymptomCount = 0;
            foreach ($breedRows as $row) {
                if ($this->normalizeSymptom($row['Symptoms'] ?? '') === $symptom) {
                    $overallSymptomCount++;
                    if (($this->normalize($row['Species'] ?? '') === $species) && ($this->normalize($row['Breed'] ?? '') === $breed)) {
                        $breedSymptomCount++;
                    }
                }
            }

            $knownForBreed = $breedTotal >= 1;
            $breedPercent = $breedTotal > 0 ? round(100 * $breedSymptomCount / $breedTotal, 1) : 0.0;
            $overallPercent = $totalRows > 0 ? round(100 * $overallSymptomCount / $totalRows, 1) : 0.0;

            $frequencyRows[] = [
                'symptom' => $symptom,
                'display_symptom' => $this->symptomLabel($symptom),
                'breed_frequency_percent' => $breedPercent,
                'overall_frequency_percent' => $overallPercent,
                'known_for_breed' => $knownForBreed,
                'breed_sample_size' => $breedTotal,
            ];
        }

        usort($frequencyRows, function ($a, $b) {
            return $b['breed_frequency_percent'] <=> $a['breed_frequency_percent'];
        });

        $ageMonths = $petData['age_months'] ?? null;
        $ageYears = $ageMonths !== null ? $ageMonths / 12 : null;

        $observationsText = "";
        if (empty($frequencyRows)) {
            $observationsText = "- No matching symptom observations were found in the supplied dataset.";
        } else {
            $lines = [];
            foreach ($frequencyRows as $item) {
                if ($item['known_for_breed']) {
                    $lines[] = sprintf("- %s: observed in %.1f%% of %d %s records in the dataset", $item['display_symptom'], $item['breed_frequency_percent'], $item['breed_sample_size'], $breed);
                } else {
                    $lines[] = sprintf("- %s: no records for this breed in the dataset; overall dataset frequency %.1f%%", $item['display_symptom'], $item['overall_frequency_percent']);
                }
            }
            $observationsText = implode("\n", $lines);
        }

        $reportedSymptomsLabels = [];
        foreach ($requested as $symptom) {
            $reportedSymptomsLabels[] = $this->symptomLabel($symptom);
        }
        $symptomsText = empty($reportedSymptomsLabels) ? "- None reported" : implode("\n", array_map(fn($s) => "- " . $s, $reportedSymptomsLabels));

        $prompt = "Species: {$species}\n" .
            "Breed: " . ($breed !== '' ? $breed : 'Not provided') . "\n" .
            "Age: " . ($ageMonths !== null ? $ageMonths . " months" : 'Not provided') . "\n" .
            "Weight: " . ((isset($petData['weight_kg']) && $petData['weight_kg'] !== null) ? $petData['weight_kg'] . " kg" : 'Not provided') . "\n\n" .
            "Reported symptoms:\n{$symptomsText}\n\n" .
            "Dataset observations:\n{$observationsText}\n\n" .
            "Instruction:\n" .
            "Provide educational pet-care guidance based on the provided\n" .
            "breed, species, age, weight, symptoms, and dataset observations.\n" .
            "Do not diagnose diseases.\n" .
            "Do not prescribe medication.\n" .
            "Recommend veterinary consultation when appropriate.\n" .
            "Explain that dataset frequencies are descriptive and cannot diagnose an individual pet.\n" .
            "Keep the guidance concise, practical, and free of greetings or role-play.\n" .
            "Do not invent or change any dataset percentages.\n" .
            "Limit your response to 120 words.";

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $key,
                'Content-Type' => 'application/json',
            ])->timeout(30)->post('https://api.groq.com/openai/v1/chat/completions', [
                'model' => $model,
                'temperature' => 0.2,
                'max_tokens' => 300,
                'messages' => [
                    ['role' => 'system', 'content' => 'You are a helpful assistant providing educational pet care guidance.'],
                    ['role' => 'user', 'content' => $prompt],
                ],
            ]);
            $response->throw();
            $json = $response->json();
            $content = $json['choices'][0]['message']['content'] ?? '';
            $content = preg_replace('/<think>.*?<\/think>/s', '', $content);
            $guidance = trim((string) $content);
        } catch (\Exception $e) {
            throw new ConnectionException('AI guidance is unavailable right now. Please try again shortly.');
        }

        return [
            'species' => $petData['species'] ?? '',
            'breed' => $petData['breed'] ?? '',
            'age_months' => $ageMonths,
            'weight_kg' => $petData['weight_kg'] ?? null,
            'reported_symptoms' => array_map(fn($s) => $this->symptomLabel($s), $requested),
            'dataset_observations' => $frequencyRows,
            'age_years' => $ageYears,
            'guidance' => $guidance,
        ];
    }
}
