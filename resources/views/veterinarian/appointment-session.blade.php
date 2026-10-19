@extends('layouts.app')

@section('title', 'Appointment Session')

@section('sidebar')
    <a href="{{ route('vet.dashboard') }}" class="sidebar-item">Dashboard</a>
    <a href="{{ route('vet.appointments') }}" class="sidebar-item active">Appointments</a>
    <a href="{{ route('vet.medical-records') }}" class="sidebar-item">Medical Records</a>
    <a href="{{ route('vet.prescriptions') }}" class="sidebar-item">E-Prescriptions</a>
    <a href="{{ route('vet.adherence-monitoring') }}" class="sidebar-item">Medication Adherence</a>
@endsection

@section('content')
    @php
        $pet = $appointment->pet;
        $owner = $pet?->owner;
        $prescriptionDrafts = old('prescriptions', [
            [
                'medication_name' => '',
                'dosage' => '',
                'frequency' => '',
                'duration' => '',
            ],
        ]);
        $systemFamilyName = $owner?->name ?? 'Not on record';
        $systemVisitType = $appointment->consultation_mode ?? 'In-clinic';
        $systemAddress = $owner?->address ?? 'Not on record';
        $systemDate = $appointment->appointment_date?->format('M d, Y') ?? 'N/A';
        $systemCell = $owner?->phone ?? 'N/A';
        $systemSpecies = $pet?->species ?? 'N/A';
        $systemBreed = $pet?->breed ?? 'N/A';
        $systemBirthday = $pet?->date_of_birth?->format('M d, Y') ?? 'N/A';
        $systemAge = $pet?->age_display ?? ($pet?->age !== null ? $pet->age.' years' : 'N/A');
        $systemSex = $pet?->sex ?? 'N/A';
        $systemColor = $pet?->color ?? 'Not on record';
        $attendingVet = $consultation?->veterinarian?->name ?? auth()->user()?->name ?? 'Veterinarian';
    @endphp

    <section class="hero-card">
        <div class="hero-row">
            <div>
                <h1 class="hero-title">Appointment Session</h1>
                <p class="hero-copy">
                    Review the patient summary, record the consultation outcome, and save the medical record and medication from one workspace.
                </p>
            </div>

            <div class="action-row">
                <a href="{{ route('vet.appointments') }}" class="btn btn-secondary">Back To Appointments</a>
            </div>
        </div>
    </section>

    @if (session('success'))
        <div class="alert alert-info">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            Please correct the highlighted fields before saving the session.
            <ul style="margin: 0.5rem 0 0 1.25rem;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="info-grid">
        <div class="summary-card">
            <span class="summary-label">Patient</span>
            <strong>{{ $pet?->name ?? 'Unknown Pet' }}</strong>
            <span>{{ $pet?->species ?? 'Unknown species' }}{{ $pet?->breed ? ' - '.$pet->breed : '' }}</span>
        </div>
        <div class="summary-card">
            <span class="summary-label">Owner</span>
            <strong>{{ $owner?->name ?? 'Unknown Owner' }}</strong>
            <span>{{ $owner?->email ?? 'No email on file' }}</span>
        </div>
        <div class="summary-card">
            <span class="summary-label">Schedule</span>
            <strong>{{ $appointment->appointment_date?->format('M j, Y g:i A') ?? 'Schedule unavailable' }}</strong>
            <span>{{ $appointment->consultation_mode ?? 'In-clinic' }}</span>
        </div>
        <div class="summary-card">
            <span class="summary-label">Status</span>
            <strong>{{ $appointment->status }}</strong>
            <span>Consultation {{ $consultation->status }}</span>
        </div>
    </div>

    <div class="info-grid" style="margin-top: 1rem;">
        <div class="detail-panel" style="grid-column: 1 / -1;">
            <span class="detail-label">Reason For Visit</span>
            <p>{{ $appointment->reason ?: 'No reason was recorded for this appointment.' }}</p>
        </div>
    </div>

    <section class="card intake-sheet" style="margin-top: 1.2rem;">
        <div class="surface-header compact-header">
            <div>
                <h2>Session Record</h2>
            </div>
        </div>

        <form action="{{ route('vet.appointments.session.store', $appointment) }}" method="POST" class="stack">
            @csrf

            <div class="intake-shell compact-intake-shell">
                <div class="intake-panel compact-intake-panel">
                    <div class="intake-row compact-row">
                        <div class="intake-label">Family Name:</div>
                        <div class="intake-value">{{ $systemFamilyName }}</div>
                    </div>

                    <div class="intake-row compact-row">
                        <div class="intake-label">Address:</div>
                        <div class="intake-value">{{ $systemAddress }}</div>
                    </div>

                    <div class="intake-row compact-row">
                        <div class="intake-label">Species:</div>
                        <div class="intake-value">{{ $systemSpecies }}</div>
                    </div>

                    <div class="intake-row compact-row">
                        <div class="intake-label">Breed:</div>
                        <div class="intake-value">{{ $systemBreed }}</div>
                    </div>

                    <div class="intake-row compact-row">
                        <div class="intake-label">Birthday:</div>
                        <div class="intake-value">{{ $systemBirthday }}</div>
                    </div>

                    <div class="intake-row compact-row">
                        <div class="intake-label">Age:</div>
                        <div class="intake-value">{{ $systemAge }}</div>
                    </div>

                    <div class="intake-row compact-row">
                        <div class="intake-label">Sex:</div>
                        <div class="intake-value">{{ $systemSex }}</div>
                    </div>

                    <div class="intake-row compact-row">
                        <div class="intake-label">Color:</div>
                        <div class="intake-value">{{ $systemColor }}</div>
                    </div>

                    <div class="intake-row compact-row intake-row--field">
                        <label class="intake-label" for="food">Food:</label>
                        <input id="food" name="food" type="text" class="field-control compact-field" value="{{ old('food') }}" placeholder="e.g. Royal Canin Adult">
                    </div>

                    <div class="intake-row compact-row intake-row--field">
                        <label class="intake-label" for="environment">Environment:</label>
                        <input id="environment" name="environment" type="text" class="field-control compact-field" value="{{ old('environment') }}" placeholder="Indoor / outdoor / cage / home setting">
                    </div>

                    <div class="intake-row compact-row intake-row--field intake-row--textarea">
                        <label class="intake-label" for="reason_for_visit">Reason for Visit:</label>
                        <div id="reason_for_visit" class="intake-value">{{ $appointment->reason_for_visit ?? 'Not recorded' }}</div>
                    </div>

                    <div class="intake-row compact-row intake-row--field intake-row--textarea">
                        <label class="intake-label" for="diagnosis">Diagnosis:</label>
                        <textarea id="diagnosis" name="diagnosis" class="field-control compact-field" placeholder="Document the diagnosis and clinical findings.">{{ old('diagnosis', $medicalRecord?->diagnosis) }}</textarea>
                        @error('diagnosis')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="intake-row compact-row intake-row--field intake-row--textarea">
                        <label class="intake-label" for="treatment_plan">Line of Treatment:</label>
                        <textarea id="treatment_plan" name="treatment_plan" class="field-control compact-field" placeholder="Outline the medical plan, medications, and follow-up steps.">{{ old('treatment_plan', $medicalRecord?->treatment_plan) }}</textarea>
                        @error('treatment_plan')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="intake-row compact-row intake-row--field">
                        <label class="intake-label" for="attending_vet">Attending Vet:</label>
                        <input id="attending_vet" name="attending_vet" type="text" class="field-control compact-field" value="{{ old('attending_vet', $attendingVet) }}" readonly>
                    </div>

                    <div class="intake-row compact-row intake-row--field">
                        <label class="intake-label" for="fee">Fee:</label>
                        <input id="fee" name="fee" type="text" class="field-control compact-field" value="{{ old('fee') }}" placeholder="₱0.00">
                        @error('fee')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="intake-panel compact-intake-panel compact-intake-panel--right">
                    <div class="intake-row compact-row">
                        <div class="intake-label">Visit type:</div>
                        <div class="intake-value">{{ $systemVisitType }}</div>
                    </div>

                    <div class="intake-row compact-row">
                        <div class="intake-label">Date:</div>
                        <div class="intake-value">{{ $systemDate }}</div>
                    </div>

                    <div class="intake-row compact-row">
                        <div class="intake-label">Cell#:</div>
                        <div class="intake-value">{{ $systemCell }}</div>
                    </div>

                    <div class="intake-row compact-row intake-row--field">
                        <label class="intake-label" for="weight">Weight:</label>
                        <input id="weight" name="weight" type="number" step="0.01" min="0" class="field-control compact-field" value="{{ old('weight', $medicalRecord?->weight) }}" placeholder="e.g. 6.5">
                        @error('weight')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="intake-row compact-row intake-row--field">
                        <label class="intake-label" for="temp">Temp:</label>
                        <input id="temp" name="temp" type="number" step="0.01" min="0" max="100" class="field-control compact-field" value="{{ old('temp', $medicalRecord?->temp) }}" placeholder="e.g. 38.6">
                        @error('temp')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="intake-row compact-row compact-row--spacer"></div>

                    <div class="intake-row compact-row intake-row--field">
                        <label class="intake-label" for="follow_up_date">Follow update</label>
                        <input id="follow_up_date" name="follow_up_date" type="date" class="field-control compact-field" value="{{ old('follow_up_date', $medicalRecord?->follow_up_date?->toDateString()) }}">
                        @error('follow_up_date')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="surface-header" style="padding-inline: 0;">
                <div>
                    <h2>Optional Prescriptions</h2>
                    <p class="section-copy">Add one or more medications now if treatment should be issued immediately after the session.</p>
                    <p class="muted-copy" style="margin-top: 0.35rem; font-size: 0.88rem;">
                        Frequency uses doses per day in Philippine time. Each scheduled dose stays confirmable for 3 hours.
                    </p>
                </div>
                <button type="button" class="btn btn-secondary" id="add-prescription-row">Add Another Prescription</button>
            </div>

            <div class="stack" id="prescription-rows">
                @foreach ($prescriptionDrafts as $index => $prescriptionDraft)
                    <div class="prescription-draft" data-prescription-row>
                        <div class="action-row" style="justify-content: space-between; align-items: center;">
                            <div class="item-title">Prescription {{ $index + 1 }}</div>
                            <button type="button" class="btn btn-secondary remove-prescription-row" @if (count($prescriptionDrafts) === 1) style="display: none;" @endif>Remove</button>
                        </div>

                        <div class="form-grid" style="margin-top: 1rem;">
                            <div class="field">
                                <label class="field-label" for="prescriptions_{{ $index }}_medication_name">Medication Name</label>
                                <input
                                    id="prescriptions_{{ $index }}_medication_name"
                                    name="prescriptions[{{ $index }}][medication_name]"
                                    data-prescription-field="medication_name"
                                    type="text"
                                    class="field-control"
                                    placeholder="Ondansetron"
                                    value="{{ $prescriptionDraft['medication_name'] ?? '' }}">
                                @error("prescriptions.$index.medication_name")
                                    <p class="field-error">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="field">
                                <label class="field-label" for="prescriptions_{{ $index }}_dosage">Dosage</label>
                                <input
                                    id="prescriptions_{{ $index }}_dosage"
                                    name="prescriptions[{{ $index }}][dosage]"
                                    data-prescription-field="dosage"
                                    type="text"
                                    class="field-control"
                                    placeholder="4 mg"
                                    value="{{ $prescriptionDraft['dosage'] ?? '' }}">
                                @error("prescriptions.$index.dosage")
                                    <p class="field-error">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="field">
                                <label class="field-label" for="prescriptions_{{ $index }}_frequency">Frequency</label>
                                <input
                                    id="prescriptions_{{ $index }}_frequency"
                                    name="prescriptions[{{ $index }}][frequency]"
                                    data-prescription-field="frequency"
                                    type="text"
                                    list="prescription-frequency-options"
                                    inputmode="numeric"
                                    class="field-control"
                                    placeholder="1 to 5"
                                    value="{{ $prescriptionDraft['frequency'] ?? '' }}">
                                @error("prescriptions.$index.frequency")
                                    <p class="field-error">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="field">
                                <label class="field-label" for="prescriptions_{{ $index }}_duration">Duration</label>
                                <input
                                    id="prescriptions_{{ $index }}_duration"
                                    name="prescriptions[{{ $index }}][duration]"
                                    data-prescription-field="duration"
                                    type="text"
                                    list="prescription-duration-options"
                                    inputmode="numeric"
                                    class="field-control"
                                    placeholder="3"
                                    value="{{ $prescriptionDraft['duration'] ?? '' }}">
                                @error("prescriptions.$index.duration")
                                    <p class="field-error">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <template id="prescription-row-template">
                <div class="prescription-draft" data-prescription-row>
                    <div class="action-row" style="justify-content: space-between; align-items: center;">
                        <div class="item-title">Prescription __NUMBER__</div>
                        <button type="button" class="btn btn-secondary remove-prescription-row">Remove</button>
                    </div>

                    <div class="form-grid" style="margin-top: 1rem;">
                        <div class="field">
                            <label class="field-label" for="prescriptions___INDEX___medication_name">Medication Name</label>
                            <input
                                id="prescriptions___INDEX___medication_name"
                                name="prescriptions[__INDEX__][medication_name]"
                                data-prescription-field="medication_name"
                                type="text"
                                class="field-control"
                                placeholder="Ondansetron">
                        </div>

                        <div class="field">
                            <label class="field-label" for="prescriptions___INDEX___dosage">Dosage</label>
                            <input
                                id="prescriptions___INDEX___dosage"
                                name="prescriptions[__INDEX__][dosage]"
                                data-prescription-field="dosage"
                                type="text"
                                class="field-control"
                                placeholder="4 mg">
                        </div>

                        <div class="field">
                            <label class="field-label" for="prescriptions___INDEX___frequency">Frequency</label>
                            <input
                                id="prescriptions___INDEX___frequency"
                                name="prescriptions[__INDEX__][frequency]"
                                data-prescription-field="frequency"
                                type="text"
                                list="prescription-frequency-options"
                                inputmode="numeric"
                                class="field-control"
                                placeholder="1 to 5">
                        </div>

                        <div class="field">
                            <label class="field-label" for="prescriptions___INDEX___duration">Duration</label>
                            <input
                                id="prescriptions___INDEX___duration"
                                name="prescriptions[__INDEX__][duration]"
                                data-prescription-field="duration"
                                type="text"
                                list="prescription-duration-options"
                                inputmode="numeric"
                                class="field-control"
                                placeholder="3">
                        </div>
                    </div>
                </div>
            </template>

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
                <button type="submit" class="btn btn-primary">Save Session</button>
                <a href="{{ route('vet.medical-records', ['patient' => $pet?->pet_id]) }}" class="btn btn-secondary">View Medical Records</a>
            </div>
        </form>
    </section>

    @if ($medicalRecord)
        <section class="card" style="margin-top: 1.2rem;">
            <div class="surface-header">
                <div>
                    <h2>Existing Prescriptions</h2>
                    <p class="section-copy">Medication already linked to medical record {{ $medicalRecord->record_id }}.</p>
                </div>
            </div>

            <div class="list-grid">
                @forelse ($medicalRecord->prescriptions as $prescription)
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
                        No medication has been issued from this session yet.
                    </div>
                @endforelse
            </div>
        </section>
    @endif
@endsection

@section('styles')
    <style>
        .compact-header {
            margin-bottom: 1rem;
            padding-bottom: 0.8rem;
            border-bottom: 1px solid rgba(23, 49, 61, 0.08);
        }

        .compact-header h2 {
            margin: 0;
            font-size: 1.35rem;
            color: var(--shell-ink);
            letter-spacing: -0.03em;
        }

        .intake-sheet {
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.96), rgba(246, 250, 250, 0.92)) !important;
            border: 1px solid rgba(23, 49, 61, 0.06) !important;
            box-shadow: 0 18px 40px rgba(18, 41, 48, 0.06) !important;
            padding: 1.25rem 1.2rem !important;
            border-radius: 1.2rem !important;
        }

        .compact-intake-shell {
            display: grid;
            grid-template-columns: minmax(0, 1.12fr) minmax(0, 0.88fr);
            gap: 0.95rem 1.5rem;
            margin-bottom: 1.2rem;
        }

        .compact-intake-panel {
            padding: 0.15rem 0;
            border: none;
            background: transparent;
            box-shadow: none;
        }

        .compact-intake-panel--right {
            padding-top: 0;
        }

        .compact-row {
            display: grid;
            grid-template-columns: minmax(120px, 170px) minmax(0, 1fr);
            gap: 0.25rem 0.8rem;
            align-items: center;
            padding: 0.22rem 0.1rem;
            border: none;
            background: transparent;
            border-radius: 0;
            margin-bottom: 0.1rem;
        }

        .intake-row--field {
            align-items: center;
        }

        .intake-row--textarea {
            grid-template-columns: minmax(120px, 170px) minmax(0, 1fr);
            align-items: start;
            margin-top: 0.32rem;
        }

        .intake-label {
            font-weight: 700;
            color: var(--shell-ink);
            line-height: 1.45;
        }

        .intake-value {
            color: var(--shell-muted);
            font-weight: 600;
            line-height: 1.45;
            word-break: break-word;
        }

        .compact-row--spacer {
            min-height: 2.8rem;
        }

        .field-control {
            width: 100%;
            min-height: 48px;
            padding: 0.8rem 1rem;
            border: 1px solid rgba(23, 49, 61, 0.12);
            border-radius: 0.95rem;
            background: rgba(255, 255, 255, 0.96);
            color: var(--shell-ink);
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.7);
            transition: border-color 0.18s ease, box-shadow 0.18s ease, transform 0.18s ease;
        }

        textarea.field-control {
            min-height: 120px;
            resize: vertical;
        }

        .field-control:focus {
            border-color: rgba(15, 139, 141, 0.58);
            box-shadow: 0 0 0 4px rgba(15, 139, 141, 0.12);
            outline: none;
        }

        .field-control.compact-field {
            width: 100%;
            min-height: 24px;
            padding: 0.15rem 0.2rem 0.22rem;
            border: none !important;
            border-bottom: 1px solid rgba(23, 49, 61, 0.12) !important;
            background: transparent !important;
            border-radius: 0;
            color: var(--shell-ink);
            box-shadow: none !important;
            font: inherit;
        }

        .field-control.compact-field:focus {
            border-bottom-color: rgba(15, 139, 141, 0.5) !important;
            box-shadow: none !important;
            outline: none;
        }

        textarea.field-control.compact-field {
            min-height: 58px;
            resize: vertical;
            line-height: 1.45;
            padding-top: 0.2rem;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 46px;
            padding: 0.76rem 1.15rem;
            border-radius: 0.95rem;
            border: none;
            font-weight: 700;
            transition: transform 0.18s ease, box-shadow 0.18s ease, opacity 0.18s ease;
        }

        .btn:hover {
            transform: translateY(-1px);
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--shell-accent), var(--shell-accent-deep));
            color: white;
            box-shadow: 0 18px 28px rgba(15, 139, 141, 0.18);
        }

        .btn-secondary {
            background: rgba(23, 49, 61, 0.04);
            color: var(--shell-ink);
            box-shadow: inset 0 0 0 1px rgba(23, 49, 61, 0.08);
        }

        .prescription-draft {
            border: 1px solid rgba(23, 49, 61, 0.08);
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.96), rgba(244, 247, 248, 0.95));
            box-shadow: 0 16px 30px rgba(23, 49, 61, 0.04);
            border-radius: 1rem;
            padding: 1rem;
        }

        @media (max-width: 768px) {
            .compact-intake-shell {
                grid-template-columns: 1fr;
            }

            .compact-row,
            .intake-row--textarea {
                grid-template-columns: minmax(100px, 140px) minmax(0, 1fr);
            }
        }
    </style>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const rowsContainer = document.getElementById('prescription-rows');
            const addButton = document.getElementById('add-prescription-row');
            const template = document.getElementById('prescription-row-template');

            if (!rowsContainer || !addButton || !template) {
                return;
            }

            const syncRows = () => {
                const rows = rowsContainer.querySelectorAll('[data-prescription-row]');

                rows.forEach((row, index) => {
                    const title = row.querySelector('.item-title');
                    const removeButton = row.querySelector('.remove-prescription-row');
                    const fields = row.querySelectorAll('[data-prescription-field]');

                    if (title) {
                        title.textContent = `Prescription ${index + 1}`;
                    }

                    fields.forEach((field) => {
                        const fieldName = field.dataset.prescriptionField;
                        const nextId = `prescriptions_${index}_${fieldName}`;
                        const label = row.querySelector(`label[for="${field.id}"]`) ?? field.closest('.field')?.querySelector('label');

                        field.id = nextId;
                        field.name = `prescriptions[${index}][${fieldName}]`;

                        if (label) {
                            label.htmlFor = nextId;
                        }
                    });

                    if (removeButton) {
                        removeButton.style.display = rows.length === 1 ? 'none' : 'inline-flex';
                    }
                });
            };

            addButton.addEventListener('click', () => {
                const index = rowsContainer.querySelectorAll('[data-prescription-row]').length;
                const html = template.innerHTML
                    .replaceAll('__INDEX__', String(index))
                    .replaceAll('__NUMBER__', String(index + 1));

                rowsContainer.insertAdjacentHTML('beforeend', html);
                syncRows();
            });

            rowsContainer.addEventListener('click', (event) => {
                const removeButton = event.target.closest('.remove-prescription-row');

                if (!removeButton) {
                    return;
                }

                const row = removeButton.closest('[data-prescription-row]');

                if (!row) {
                    return;
                }

                row.remove();
                syncRows();
            });

            syncRows();
        });
    </script>
@endsection
