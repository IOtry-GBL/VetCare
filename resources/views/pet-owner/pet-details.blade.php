@extends('layouts.app')

@section('title', $pet->name . ' - Pet Information')

@section('sidebar')
    <a href="{{ route('pet-owner.dashboard') }}" class="sidebar-item">Dashboard</a>
    <a href="{{ route('pet-owner.pets') }}" class="sidebar-item active">My Pets</a>
    <a href="{{ route('pet-owner.appointments') }}" class="sidebar-item">Appointments</a>
    <a href="{{ route('pet-owner.prescriptions') }}" class="sidebar-item">Prescriptions</a>
    <a href="{{ route('pet-owner.notifications') }}" class="sidebar-item">Notifications</a>
    <a href="#" class="sidebar-item" onclick="openPetCareAI(event)">Ask Pet Care AI</a>
@endsection

@section('content')
    <section class="hero-card">
        <div class="hero-row">
            <div>
                <h1 class="hero-title">{{ $pet->name }}</h1>
                <p class="hero-copy">Pet information</p>
            </div>
        </div>
    </section>

    <section class="card" style="max-width: 720px;">
        <div style="display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 1rem;">
            <div>
                <strong>Species</strong>
                <p>{{ $pet->species }}</p>
            </div>
            <div>
                <strong>Breed</strong>
                <p>{{ $pet->breed }}</p>
            </div>
            <div>
                <strong>Color</strong>
                <p>{{ $pet->color ?? 'N/A' }}</p>
            </div>
            <div>
                <strong>Age</strong>
                <p>{{ $pet->age_display ?? 'N/A' }}</p>
            </div>
            <div>
                <strong>Weight</strong>
                <p>{{ $pet->weight ? $pet->weight . ' kg' : 'N/A' }}</p>
            </div>
            <div>
                <strong>Sex</strong>
                <p>{{ ucfirst($pet->sex) }}</p>
            </div>
            <div>
                <strong>Total Visits</strong>
                <p>{{ $pet->appointments->whereIn('status', ['Approved', 'Completed'])->count() }}</p>
            </div>
        </div>

        <div style="display: flex; flex-wrap: wrap; gap: 0.75rem; margin-top: 2rem;">
            <a href="{{ route('pet-owner.pets.edit', $pet->id) }}" class="btn btn-primary">Edit Pet</a>
            <a href="{{ route('pet-owner.pets.confirm-delete', $pet->id) }}" class="btn" style="background-color: #ef4444; color: white;">Delete Pet</a>
            <a href="{{ route('pet-owner.pets') }}" class="btn btn-secondary">Back to My Pets</a>
        </div>
    </section>
@endsection
