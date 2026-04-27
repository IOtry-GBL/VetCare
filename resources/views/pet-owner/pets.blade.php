@extends('layouts.app')

@section('sidebar')
    <a href="{{ route('pet-owner.dashboard') }}" class="sidebar-item"> Dashboard</a>
    <a href="{{ route('pet-owner.pets') }}" class="sidebar-item active"> My Pets</a>
    <a href="{{ route('pet-owner.appointments') }}" class="sidebar-item"> Appointments</a>

    <a href="{{ route('pet-owner.prescriptions') }}" class="sidebar-item"> Prescriptions</a>
    <a href="{{ route('pet-owner.notifications') }}" class="sidebar-item"> Notifications</a>
    <a href="#" class="sidebar-item" onclick="openPetCareAI(event)">Ask Pet Care AI</a>
@endsection

@section('content')
    <div class="card">
        <h1>My Pets</h1>
        <a href="{{ route('pet-owner.pets.create') }}" class="btn btn-primary">+ Add New Pet</a>
    </div>

    <div style="display: grid; gap: 1.5rem;">
        @forelse($pets as $pet)
            <div class="card" style="border-left: 4px solid #667eea;">
                <div style="display: flex; justify-content: space-between; align-items: start;">
                    <div>
                        <h2 style="margin: 0;">{{ $pet->name }}</h2>
                        <p style="color: #6b7280; margin: 0.5rem 0 0 0;">{{ $pet->breed }} • {{ $pet->age }} years old • {{ $pet->weight }} kg</p>
                        <div style="margin-top: 1rem;">
                            <span class="badge badge-monitor">Active</span>
                        </div>
                    </div>
                    <div>
                        <a href="{{ route('pet-owner.pets.edit', $pet->id) }}" class="btn btn-secondary" style="font-size: 0.875rem; padding: 0.5rem 1rem;">Edit</a>
                    </div>
                </div>
                <hr style="margin: 1rem 0; border: none; border-top: 1px solid #e5e7eb;">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                    <div>
                        <div style="font-size: 0.875rem; color: #6b7280; font-weight: 500;">Species</div>
                        <div>{{ $pet->species }}</div>
                    </div>
                    <div>
                        <div style="font-size: 0.875rem; color: #6b7280; font-weight: 500;">Breed</div>
                        <div>{{ $pet->breed }}</div>
                    </div>
                    <div>
                        <div style="font-size: 0.875rem; color: #6b7280; font-weight: 500;">Age</div>
                        <div>{{ $pet->age }} years</div>
                    </div>
                    <div>
                        <div style="font-size: 0.875rem; color: #6b7280; font-weight: 500;">Weight</div>
                        <div>{{ $pet->weight }} kg</div>
                    </div>
                    <div>
                        <div style="font-size: 0.875rem; color: #6b7280; font-weight: 500;">Sex</div>
                        <div>{{ ucfirst($pet->sex) }}</div>
                    </div>
                    <div>
                        <div style="font-size: 0.875rem; color: #6b7280; font-weight: 500;">Total Visits</div>
                        <div>{{ $pet->appointments->whereIn('status', ['Approved', 'Completed'])->count() }}</div>
                    </div>
                </div>
                
                @if($pet->medicalRecords->count() > 0)
                    <hr style="margin: 1rem 0; border: none; border-top: 1px solid #e5e7eb;">
                    <div>
                        <strong>Medical Records ({{ $pet->medicalRecords->count() }})</strong>
                        <div style="margin-top: 0.5rem; display: grid; gap: 0.5rem;">
                            @foreach($pet->medicalRecords->take(3) as $record)
                                <div style="padding: 0.75rem; background: #f3f4f6; border-radius: 0.375rem; font-size: 0.875rem;">
                                    <strong>{{ $record->record_type }}</strong> - {{ $record->description }}
                                    <p style="margin: 0.25rem 0 0 0; color: #6b7280; font-size: 0.8rem;">{{ $record->created_at->format('M d, Y') }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <hr style="margin: 1rem 0; border: none; border-top: 1px solid #e5e7eb;">
                
            </div>
        @empty
            <div style="padding: 2rem; text-align: center; background: #f9fafb; border-radius: 0.375rem;">
                <p style="color: #6b7280; font-size: 1.125rem;">No pets found. <a href="#" style="color: #667eea; text-decoration: none;">Add your first pet</a></p>
            </div>
        @endforelse
    </div>
@endsection
