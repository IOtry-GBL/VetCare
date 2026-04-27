@extends('layouts.app')

@section('title', 'E-Prescriptions')

@section('sidebar')
    <a href="{{ route('vet.dashboard') }}" class="sidebar-item">Dashboard</a>
    <a href="{{ route('vet.appointments') }}" class="sidebar-item">Appointments</a>

    <a href="{{ route('vet.medical-records') }}" class="sidebar-item">Medical Records</a>
    <a href="{{ route('vet.prescriptions') }}" class="sidebar-item active">E-Prescriptions</a>
    <a href="{{ route('vet.adherence-monitoring') }}" class="sidebar-item">Medication Adherence</a>
@endsection

@section('content')
    @php
        $selectedRecordId = old('record_id', optional($selectedRecord)->record_id);
    @endphp

    <section class="hero-card">
        <div class="hero-row">
            <div>
                <h1 class="hero-title">E-Prescriptions</h1>
                <p class="hero-copy">
                    Review clinic medical records, prepare medication details from live patient data, and track every prescription that has already been issued.
                </p>
            </div>

            <div class="metric-grid" style="min-width: min(100%, 460px);">
                <div class="metric-card">
                    <span class="metric-label">Medical Records</span>
                    <strong class="metric-value">{{ $medicalRecords->count() }}</strong>
                </div>
                <div class="metric-card">
                    <span class="metric-label">Patients</span>
                    <strong class="metric-value">{{ $patientCount }}</strong>
                </div>
                <div class="metric-card">
                    <span class="metric-label">Issued Today</span>
                    <strong class="metric-value">{{ $issuedTodayCount }}</strong>
                </div>
                <div class="metric-card">
                    <span class="metric-label">Total Prescriptions</span>
                    <strong class="metric-value">{{ $prescriptions->count() }}</strong>
                </div>
            </div>
        </div>
    </section>

    @if (session('success'))
        <div class="alert alert-info">
            {{ session('success') }}
        </div>
    @endif

    <div class="prescription-layout">
        <section class="card">
            <div class="surface-header">
                <div>
                    <h2>Medical Records</h2>
                    <p class="section-copy">Choose a record to review the case and prefill the prescription form.</p>
                </div>
            </div>

            <div class="stack">
                @forelse ($medicalRecords as $record)
                    @php
                        $pet = $record->pet;
                        $owner = $pet?->owner;
                        $isSelected = optional($selectedRecord)->record_id === $record->record_id;
                        $recordSummary = $record->diagnosis ?: $record->treatment_plan ?: $record->vaccination_notes;
                    @endphp

                    <a href="{{ route('vet.prescriptions', ['record' => $record->record_id]) }}" class="record-selector{{ $isSelected ? ' record-selector-active' : '' }}">
                        <div class="action-row">
                            <div>
                                <div class="item-title">{{ $pet?->name ?? 'Unknown Pet' }}</div>
                                <p class="item-copy" style="margin-top: 0.3rem;">
                                    {{ $pet?->species ?? 'Unknown species' }}{{ $pet?->breed ? ' - '.$pet->breed : '' }}
                                </p>
                            </div>
                            <span class="pill {{ $record->prescriptions->count() > 0 ? 'pill-success' : 'pill-neutral' }}">
                                {{ $record->prescriptions->count() }} Rx
                            </span>
                        </div>

                        <p class="item-copy" style="margin-top: 0.8rem;">Owner: {{ $owner?->name ?? 'Unknown Owner' }}</p>
                        <p class="item-copy" style="margin-top: 0.35rem;">{{ $recordSummary ? \Illuminate\Support\Str::limit($recordSummary, 130) : 'No diagnosis or treatment notes recorded yet.' }}</p>
                        <p class="item-copy" style="margin-top: 0.45rem;">
                            Record {{ $record->record_id }}
                            @if ($record->follow_up_date)
                                / Follow-up {{ $record->follow_up_date->format('M j, Y') }}
                            @endif
                        </p>
                    </a>
                @empty
                    <div class="empty-state">
                        No medical records are linked to your appointment sessions yet.
                    </div>
                @endforelse
            </div>
        </section>

        <section class="stack">
            <div class="card">
                <div class="surface-header">
                    <div>
                        <h2>Create Prescription</h2>
                        <p class="section-copy">Select a medical record and save a new prescription directly into the clinic data.</p>
                    </div>
                </div>

                @if ($selectedRecord)
                    @php
                        $selectedPet = $selectedRecord->pet;
                        $selectedOwner = $selectedPet?->owner;
                    @endphp

                    <div class="info-grid">
                        <div class="summary-card">
                            <span class="summary-label">Patient</span>
                            <strong>{{ $selectedPet?->name ?? 'Unknown Pet' }}</strong>
                            <span>{{ $selectedPet?->species ?? 'Unknown species' }}{{ $selectedPet?->breed ? ' - '.$selectedPet->breed : '' }}</span>
                        </div>
                        <div class="summary-card">
                            <span class="summary-label">Owner</span>
                            <strong>{{ $selectedOwner?->name ?? 'Unknown Owner' }}</strong>
                            <span>Record {{ $selectedRecord->record_id }}</span>
                        </div>
                        <div class="summary-card">
                            <span class="summary-label">Diagnosis</span>
                            <strong>{{ $selectedRecord->diagnosis ?: 'No diagnosis recorded' }}</strong>
                            <span>Created {{ $selectedRecord->created_at?->format('M j, Y') ?? 'Unknown date' }}</span>
                        </div>
                        <div class="summary-card">
                            <span class="summary-label">Follow-Up</span>
                            <strong>{{ $selectedRecord->follow_up_date ? $selectedRecord->follow_up_date->format('M j, Y') : 'Not scheduled' }}</strong>
                            <span>{{ $selectedRecord->prescriptions->count() }} existing prescription(s)</span>
                        </div>
                    </div>

                    <div class="info-grid" style="margin-top: 1rem;">
                        <div class="detail-panel">
                            <span class="detail-label">Treatment Plan</span>
                            <p>{{ $selectedRecord->treatment_plan ?: 'No treatment plan recorded.' }}</p>
                        </div>
                        <div class="detail-panel">
                            <span class="detail-label">Vaccination Notes</span>
                            <p>{{ $selectedRecord->vaccination_notes ?: 'No vaccination notes recorded.' }}</p>
                        </div>
                    </div>

                    <form action="{{ route('vet.prescriptions.store') }}" method="POST" class="stack" style="margin-top: 1.2rem;">
                        @csrf

                        <div class="field">
                            <label class="field-label" for="record_id">Medical Record</label>
                            <select id="record_id" name="record_id" class="field-control">
                                @foreach ($medicalRecords as $record)
                                    <option value="{{ $record->record_id }}" @selected((string) $selectedRecordId === (string) $record->record_id)>
                                        {{ $record->pet?->name ?? 'Unknown Pet' }} - Record {{ $record->record_id }}
                                    </option>
                                @endforeach
                            </select>
                            @error('record_id')
                                <p class="field-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="form-grid">
                            <div class="field">
                                <label class="field-label" for="medication_name">Medication Name</label>
                                <input id="medication_name" name="medication_name" type="text" class="field-control" placeholder="Amoxicillin" value="{{ old('medication_name') }}">
                                @error('medication_name')
                                    <p class="field-error">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="field">
                                <label class="field-label" for="dosage">Dosage</label>
                                <input id="dosage" name="dosage" type="text" class="field-control" placeholder="250 mg" value="{{ old('dosage') }}">
                                @error('dosage')
                                    <p class="field-error">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="field">
                                <label class="field-label" for="frequency">Frequency</label>
                                <input
                                    id="frequency"
                                    name="frequency"
                                    type="text"
                                    list="prescription-frequency-options"
                                    inputmode="numeric"
                                    class="field-control"
                                    placeholder="1 to 5"
                                    value="{{ old('frequency') }}">
                                <p class="muted-copy" style="font-size: 0.85rem; margin-top: 0.35rem;">
                                    Doses per day. The schedule uses Philippine time and opens each confirmation for 3 hours.
                                </p>
                                @error('frequency')
                                    <p class="field-error">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="field">
                                <label class="field-label" for="duration">Duration</label>
                                <input
                                    id="duration"
                                    name="duration"
                                    type="text"
                                    list="prescription-duration-options"
                                    inputmode="numeric"
                                    class="field-control"
                                    placeholder="7"
                                    value="{{ old('duration') }}">
                                <p class="muted-copy" style="font-size: 0.85rem; margin-top: 0.35rem;">
                                    Enter the number of treatment days, or choose one from the list.
                                </p>
                                @error('duration')
                                    <p class="field-error">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <datalist id="prescription-frequency-options">
                            <option value="1" label="Once daily"></option>
                            <option value="2" label="Twice daily"></option>
                            <option value="3" label="3 times daily"></option>
                            <option value="4" label="4 times daily"></option>
                            <option value="5" label="5 times daily"></option>
                        </datalist>

                        <datalist id="prescription-duration-options">
                            <option value="1"></option>
                            <option value="3"></option>
                            <option value="5"></option>
                            <option value="7"></option>
                            <option value="10"></option>
                            <option value="14"></option>
                            <option value="30"></option>
                        </datalist>

                        <div class="action-row">
                            <button type="submit" class="btn btn-primary">Create Prescription</button>
                        </div>
                    </form>
                @else
                    <div class="empty-state">
                        Add an appointment-session medical record first so you can issue e-prescriptions from this screen.
                    </div>
                @endif
            </div>

            @if ($selectedRecord)
                <div class="card">
                    <div class="surface-header">
                        <div>
                            <h2>Existing Prescriptions</h2>
                            <p class="section-copy">Medication history linked to medical record {{ $selectedRecord->record_id }}.</p>
                        </div>
                    </div>

                    <div class="list-grid">
                        @forelse ($selectedRecord->prescriptions as $prescription)
                            <article class="list-card">
                                <div>
                                    <div class="item-title">{{ $prescription->medication_name }}</div>
                                    <p class="item-copy" style="margin-top: 0.35rem;">
                                        {{ $prescription->dosage }} / {{ $prescription->frequency }} / {{ $prescription->duration }}
                                    </p>
                                </div>
                                <div class="action-row">
                                    <span class="pill pill-success">{{ $prescription->issued_at?->format('M j, Y') ?? 'Issued' }}</span>
                                </div>
                            </article>
                        @empty
                            <div class="empty-state">
                                No e-prescriptions have been issued for this record yet.
                            </div>
                        @endforelse
                    </div>
                </div>
            @endif
        </section>
    </div>

    <section class="card">
        <div class="surface-header">
            <div>
                <h2>Recent E-Prescriptions</h2>
                <p class="section-copy">Live medication data pulled from the e-prescriptions table for your patients.</p>
            </div>
        </div>

        <div class="table-wrap">
            <table class="app-table">
                <thead>
                    <tr>
                        <th>Patient</th>
                        <th>Medical Record</th>
                        <th>Medication</th>
                        <th>Dosage</th>
                        <th>Frequency</th>
                        <th>Duration</th>
                        <th>Issued</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($prescriptions as $prescription)
                        @php
                            $record = $prescription->medicalRecord;
                            $pet = $record?->pet;
                            $owner = $pet?->owner;
                            $recordLine = $record?->diagnosis ?: $record?->treatment_plan ?: 'No medical summary recorded.';
                        @endphp
                        <tr>
                            <td>
                                <strong style="display: block;">{{ $pet?->name ?? 'Unknown Pet' }}</strong>
                                <span class="muted-copy" style="font-size: 0.88rem;">
                                    {{ $pet?->species ?? 'Unknown species' }}{{ $owner ? ' / '.$owner->name : '' }}
                                </span>
                            </td>
                            <td>
                                <strong style="display: block;">Record {{ $record?->record_id ?? 'N/A' }}</strong>
                                <span class="muted-copy" style="font-size: 0.88rem;">{{ \Illuminate\Support\Str::limit($recordLine, 90) }}</span>
                            </td>
                            <td>{{ $prescription->medication_name }}</td>
                            <td>{{ $prescription->dosage }}</td>
                            <td>{{ $prescription->frequency }}</td>
                            <td>{{ $prescription->duration }}</td>
                            <td>{{ $prescription->issued_at?->format('M j, Y g:i A') ?? 'Unknown date' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="muted-copy" style="text-align: center;">No e-prescriptions have been created yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection

@section('styles')
    <style>
        .prescription-layout {
            display: grid;
            grid-template-columns: minmax(300px, 0.95fr) minmax(420px, 1.2fr);
            gap: 1.4rem;
            align-items: start;
        }

        .record-selector {
            display: block;
            padding: 1.1rem;
            border: 1px solid var(--shell-line);
            border-radius: var(--shell-radius-lg);
            background: rgba(255, 255, 255, 0.86);
            transition: transform 0.18s ease, border-color 0.18s ease, box-shadow 0.18s ease;
        }

        .record-selector:hover {
            transform: translateY(-1px);
            border-color: rgba(15, 139, 141, 0.26);
            box-shadow: 0 14px 30px rgba(23, 49, 61, 0.08);
        }

        .record-selector-active {
            border-color: rgba(15, 139, 141, 0.4);
            background: linear-gradient(180deg, rgba(15, 139, 141, 0.08), rgba(255, 255, 255, 0.92));
        }

        @media (max-width: 1100px) {
            .prescription-layout {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endsection
