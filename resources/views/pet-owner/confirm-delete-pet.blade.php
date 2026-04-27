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
    <div class="card" style="border-left: 4px solid #ef4444; background-color: #fef2f2;">
        <h1>Delete Pet - Confirmation Required</h1>
        <p style="color: #991b1b; font-weight: 500;">This action cannot be undone. Please confirm you want to permanently delete this pet.</p>
    </div>

    <div class="card" style="max-width: 600px;">
        <div style="padding: 1.5rem; background-color: #f9fafb; border-radius: 0.375rem; margin-bottom: 2rem; border-left: 4px solid #ef4444;">
            <div style="display: grid; gap: 1rem;">
                <div>
                    <div style="font-size: 0.875rem; color: #6b7280; font-weight: 500;">Pet Name</div>
                    <div style="font-size: 1.125rem; font-weight: 600;">{{ $pet->name }}</div>
                </div>
                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem;">
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
                        <div style="font-size: 0.875rem; color: #6b7280; font-weight: 500;">Sex</div>
                        <div>{{ ucfirst($pet->sex) }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div style="background-color: #fef2f2; padding: 1.5rem; border-radius: 0.375rem; margin-bottom: 2rem;">
            <p style="margin: 0; color: #991b1b;">
                <strong>Warning:</strong> Deleting {{ $pet->name }} will permanently remove all associated medical records, appointments, and prescriptions. This cannot be reversed.
            </p>
        </div>

        <div style="display: flex; gap: 1rem; align-items: center;">
            <form action="{{ route('pet-owner.pets.delete', $pet->id) }}" method="POST" style="display: inline;">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn" style="background-color: #ef4444; color: white; padding: 0.75rem 1.5rem; border: none; border-radius: 0.375rem; cursor: pointer; font-weight: 500;">
                    Yes, Delete {{ $pet->name }}
                </button>
            </form>
            <a href="{{ route('pet-owner.pets.edit', $pet->id) }}" class="btn btn-secondary" style="padding: 0.75rem 1.5rem;">Cancel</a>
        </div>
    </div>
@endsection
