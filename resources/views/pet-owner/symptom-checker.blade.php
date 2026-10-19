@extends('layouts.app')

@section('sidebar')
    <a href="{{ route('pet-owner.dashboard') }}" class="sidebar-item">Dashboard</a>
    <a href="{{ route('pet-owner.pets') }}" class="sidebar-item">My Pets</a>
    <a href="{{ route('pet-owner.appointments') }}" class="sidebar-item">Appointments</a>
    <a href="{{ route('pet-owner.prescriptions') }}" class="sidebar-item">Prescriptions</a>
    <a href="{{ route('adherence.dashboard') }}" class="sidebar-item">Medication Reminders</a>
    <a href="{{ route('pet-owner.notifications') }}" class="sidebar-item">Notifications</a>
    <a href="{{ route('pet-owner.symptom-checker') }}" class="sidebar-item active">Pet Care AI</a>
@endsection

@section('content')
    <div class="card">
        <h1>AI Pet Care Guidance</h1>
        <p>Select a pet and choose the symptoms you are seeing. The list contains symptoms from the supplied datasets.</p>
    </div>

    @if ($pets->isEmpty())
        <div class="card">
            <h2>No pets available</h2>
            <p>Add a pet profile before starting an AI check.</p>
            <a class="btn btn-primary" href="{{ route('pet-owner.pets.create') }}">Add a pet</a>
        </div>
    @else
        <div class="card">
            <form method="POST" action="{{ route('pet-owner.symptom-checker.submit') }}" style="display: grid; gap: 1rem; max-width: 760px;">
                @csrf
                <label for="pet_id" style="font-weight: 600;">Pet to scan</label>
                <select id="pet_id" name="pet_id" required style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 0.375rem;">
                    <option value="">Choose a pet</option>
                    @foreach ($pets as $petOption)
                        <option value="{{ $petOption->pet_id }}" @selected((string) old('pet_id', $selectedPet?->pet_id) === (string) $petOption->pet_id)>
                            {{ $petOption->pet_name }} · {{ $petOption->species }} · {{ $petOption->breed ?: 'Breed not set' }}
                        </option>
                    @endforeach
                </select>
                @error('pet_id') <p style="margin: -0.5rem 0 0; color: #b42318;">{{ $message }}</p> @enderror

                <label for="symptom-search" style="font-weight: 600;">Search dataset symptoms</label>
                <div style="display: flex; gap: 0.5rem; max-width: 560px;">
                    <input id="symptom-search" type="search" placeholder="Search symptoms" style="flex: 1; min-width: 0; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 0.375rem;">
                    <button id="symptom-search-button" type="button" class="btn btn-secondary">Search</button>
                </div>

                <fieldset style="margin: 0; padding: 0; border: 0;">
                    <legend style="font-weight: 600; margin-bottom: 0.5rem;">Current symptoms</legend>
                    <div id="symptom-options" style="max-width: 760px; max-height: 280px; overflow-y: auto; border: 1px solid #d1d5db; border-radius: 0.375rem; padding: 0.5rem; display: grid; grid-template-columns: repeat(auto-fit, minmax(190px, 1fr)); gap: 0.25rem 0.75rem;">
                        @foreach ($availableSymptoms as $symptom)
                            <label class="symptom-option" data-symptom="{{ $symptom }}" style="display: flex; align-items: center; gap: 0.5rem; padding: 0.5rem; cursor: pointer;">
                                <input type="checkbox" name="symptoms[]" value="{{ $symptom }}" @checked(in_array($symptom, $selectedSymptoms, true))>
                                <span>{{ ucwords($symptom) }}</span>
                            </label>
                        @endforeach
                    </div>
                </fieldset>
                <p id="symptom-selection-count" aria-live="polite" style="margin: -0.5rem 0 0; color: #667085;">{{ count($selectedSymptoms) }} selected · choose up to 20</p>
                @if (empty($availableSymptoms))
                    <p style="margin: -0.5rem 0 0; color: #667085;">The dataset symptom list could not be loaded.</p>
                @endif
                @error('symptoms') <p style="margin: -0.5rem 0 0; color: #b42318;">{{ $message }}</p> @enderror
                @foreach ($errors->get('symptoms.*') as $messages)
                    @foreach ($messages as $message)
                        <p style="margin: -0.5rem 0 0; color: #b42318;">{{ $message }}</p>
                    @endforeach
                @endforeach

                <div>
                    <button type="submit" class="btn btn-primary" @disabled(empty($availableSymptoms))>Get guidance</button>
                </div>
            </form>
        </div>
    @endif

    @if ($aiError)
        <div class="card" role="alert" style="border-left: 4px solid #b42318;">
            <h2>Could not reach the AI service</h2>
            <p>{{ $aiError }}</p>
        </div>
    @endif

    @if ($selectedPet && $guidance)
        <div class="card">
            <h2>{{ $selectedPet->pet_name }}'s care guidance</h2>
            <p><strong>Species:</strong> {{ $guidance['species'] }}</p>
            <p><strong>Breed:</strong> {{ $guidance['breed'] ?: 'Not provided' }}</p>
            <p><strong>Age:</strong> {{ $guidance['age_months'] === null ? 'Not provided' : $guidance['age_months'].' months' }}</p>
            <p><strong>Weight:</strong> {{ $guidance['weight_kg'] === null ? 'Not provided' : $guidance['weight_kg'].' kg' }}</p>
        </div>

        <div class="card">
            <h2>Reported symptoms</h2>
            @if (!empty($guidance['reported_symptoms']))
                <ul>
                    @foreach ($guidance['reported_symptoms'] as $symptom)
                        <li>{{ $symptom }}</li>
                    @endforeach
                </ul>
            @endif
        </div>

        <div class="card">
            <h2>Dataset observations</h2>
            @if (!empty($guidance['dataset_observations']))
                <ul>
                    @foreach ($guidance['dataset_observations'] as $item)
                        <li>{{ $item['display_symptom'] }}: observed in {{ number_format($item['breed_frequency_percent'], 2) }}% of similar {{ $guidance['breed'] ?: $guidance['species'] }} records</li>
                    @endforeach
                </ul>
            @else
                <p>No matching symptom observations were found in the supplied dataset.</p>
            @endif
        </div>

        <div class="card">
            <h2>Educational pet-care guidance</h2>
            <div style="white-space: pre-line;">{{ $guidance['guidance'] }}</div>
            <p style="color: #667085;">Dataset frequencies describe past records; they cannot diagnose an individual pet.</p>
        </div>
    @endif
@endsection

@push('scripts')
    <script>
        (() => {
            const searchInput = document.getElementById('symptom-search');
            const searchButton = document.getElementById('symptom-search-button');
            const options = [...document.querySelectorAll('.symptom-option')];
            const count = document.getElementById('symptom-selection-count');

            if (!searchInput || !searchButton || !count) return;

            const filterOptions = () => {
                const query = searchInput.value.trim().toLowerCase();
                options.forEach((option) => {
                    option.style.display = option.dataset.symptom.includes(query) ? '' : 'none';
                });
            };

            const updateCount = () => {
                const selected = document.querySelectorAll('input[name="symptoms[]"]:checked').length;
                count.textContent = `${selected} selected · choose up to 20`;
            };

            searchButton.addEventListener('click', filterOptions);
            searchInput.addEventListener('keydown', (event) => {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    filterOptions();
                }
            });
            document.querySelectorAll('input[name="symptoms[]"]').forEach((checkbox) => {
                checkbox.addEventListener('change', updateCount);
            });
        })();
    </script>
@endpush