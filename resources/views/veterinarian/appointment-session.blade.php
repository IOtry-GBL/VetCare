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
        <div class="detail-panel">
            <span class="detail-label">Reason For Visit</span>
            <p>{{ $appointment->reason ?: 'No reason was recorded for this appointment.' }}</p>
        </div>
        <div class="detail-panel">
            <span class="detail-label">Chief Complaint</span>
            <p>{{ $consultation->chief_complaint ?: 'No chief complaint has been recorded yet.' }}</p>
        </div>
    </div>

    <section class="card" style="margin-top: 1.2rem;">
        <div class="surface-header">
            <div>
                <h2>Session Record</h2>
                <p class="section-copy">Complete the consultation details below and save the appointment as completed.</p>
            </div>
        </div>

        <form action="{{ route('vet.appointments.session.store', $appointment) }}" method="POST" class="stack">
            @csrf

            <div class="form-grid">
                <div class="field">
                    <label class="field-label" for="chief_complaint">Chief Complaint</label>
                    <textarea id="chief_complaint" name="chief_complaint" class="field-control" placeholder="Summarize the main complaint for this session.">{{ old('chief_complaint', $consultation->chief_complaint ?: $appointment->reason) }}</textarea>
                    @error('chief_complaint')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="field">
                    <label class="field-label" for="consultation_notes">Consultation Notes</label>
                    <textarea id="consultation_notes" name="consultation_notes" class="field-control" placeholder="Document observations, exam findings, and owner instructions.">{{ old('consultation_notes', $consultation->consultation_notes) }}</textarea>
                    @error('consultation_notes')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="form-grid">
                <div class="field">
                    <label class="field-label" for="diagnosis">Diagnosis</label>
                    <textarea id="diagnosis" name="diagnosis" class="field-control" placeholder="Acute gastritis">{{ old('diagnosis', $medicalRecord?->diagnosis) }}</textarea>
                    @error('diagnosis')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="field">
                    <label class="field-label" for="treatment_plan">Treatment Plan</label>
                    <textarea id="treatment_plan" name="treatment_plan" class="field-control" placeholder="Outline medications, diet, and home-care instructions.">{{ old('treatment_plan', $medicalRecord?->treatment_plan) }}</textarea>
                    @error('treatment_plan')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="form-grid">
                <div class="field">
                    <label class="field-label" for="vaccination_notes">Vaccination Notes</label>
                    <textarea id="vaccination_notes" name="vaccination_notes" class="field-control" placeholder="Record any vaccine status updates or reminders.">{{ old('vaccination_notes', $medicalRecord?->vaccination_notes) }}</textarea>
                    @error('vaccination_notes')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="field">
                    <label class="field-label" for="follow_up_date">Follow-Up Date</label>
                    <input
                        id="follow_up_date"
                        name="follow_up_date"
                        type="date"
                        class="field-control"
                        value="{{ old('follow_up_date', $medicalRecord?->follow_up_date?->toDateString()) }}">
                    @error('follow_up_date')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
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
        .prescription-draft {
            padding: 1.1rem;
            border: 1px solid var(--shell-line);
            border-radius: var(--shell-radius-lg);
            background: rgba(255, 255, 255, 0.7);
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
