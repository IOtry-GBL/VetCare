@php
    $pet = $record->pet;
    $owner = $pet?->owner;
    $consultation = $record->consultation;
    $appointment = $consultation?->appointment;
    $printField = static fn (string $label, mixed $value) => [
        'label' => $label,
        'value' => filled($value) ? $value : '',
    ];

    $leftFields = [
        $printField('Name', $owner?->name),
        $printField('Address', $owner?->address),
        $printField('Species', $pet?->species),
        $printField('Breed', $pet?->breed),
        $printField('Date of Birth', $pet?->date_of_birth?->format('M d, Y')),
        $printField('Age', $pet?->age_display ?? ($pet?->age !== null ? $pet->age.' years' : null)),
        $printField('Gender', $pet?->sex),
        $printField('Color', $pet?->color),
        $printField('Food', $record->food),
        $printField('Environment', $record->environment),
    ];

    $middleFields = [
        $printField("Pet's Name", $pet?->name),
        $printField('Visit Type', $appointment?->consultation_mode),
    ];

    $rightFields = [
        $printField('Date', $consultation?->consultation_date?->format('M d, Y') ?? $appointment?->appointment_date?->format('M d, Y')),
        $printField('Cell No.', $owner?->phone ?? $owner?->contact_no),
        $printField('Weight', $record->weight !== null ? $record->weight.' kg' : null),
        $printField('Temp', $record->temp !== null ? $record->temp.' °C' : null),
    ];

    $history = collect([
        filled($appointment?->reason_for_visit) ? 'Reason for Visit: '.$appointment->reason_for_visit : null,
        filled($consultation?->additional_reason_for_visit) ? 'Additional Reason: '.$consultation->additional_reason_for_visit : null,
        filled($record->diagnosis) ? 'Diagnosis: '.$record->diagnosis : null,
    ])->filter()->implode(' · ');
@endphp

<section class="print-sheet medical-record-print-sheet">
    @include('components.print-clinic-header')

    <div class="medical-record-patient-grid">
        @foreach ([$leftFields, $middleFields, $rightFields] as $column)
            <div class="medical-record-patient-column">
                @foreach ($column as $field)
                    <div class="print-field">
                        <span>{{ $field['label'] }}:</span>
                        <strong>{{ $field['value'] }}</strong>
                    </div>
                @endforeach
            </div>
        @endforeach
    </div>

    <div class="medical-record-narrative">
        <div>
            <h3>Case / History</h3>
            <p>{{ $history ?: 'Not recorded' }}</p>
        </div>
        <div>
            <h3>Line of Treatment</h3>
            <p>{{ $record->treatment_plan ?: 'Not recorded' }}</p>
        </div>
        <div>
            <h3>Recommendations</h3>
            <p>{{ collect([$consultation?->consultation_notes, $record->vaccination_notes])->filter()->implode(' ') ?: 'Not recorded' }}</p>
        </div>
        <div class="print-follow-up">
            <strong>Date of follow-up checkup:</strong>
            <span>{{ $record->follow_up_date?->format('M d, Y') ?? 'Not scheduled' }}</span>
        </div>
        <div class="print-follow-up">
            <strong>Attending Vet:</strong>
            <span>{{ $consultation?->veterinarian?->name ? 'Dr. '.$consultation->veterinarian->name : '' }}</span>
        </div>
        <div class="print-follow-up">
            <strong>Fee:</strong>
            <span>{{ $record->fee !== null ? '₱'.number_format($record->fee, 2) : '' }}</span>
        </div>
    </div>

</section>
