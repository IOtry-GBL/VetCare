@extends('layouts.app')

@section('sidebar')
    @include('super-admin.sidebar')
@endsection

@section('content')
    <div class="card">
        <h1 style="margin: 0;">Super Admin Users</h1>
        <p style="margin: 0.5rem 0 0 0; color: #6b7280;">
            Review account distribution and inspect everyone registered in the platform.
        </p>
    </div>

    <div class="grid" style="grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));">
        <div class="widget">
            <div class="widget-title">Pet Owners</div>
            <div class="widget-value">{{ $roleStats['owners'] }}</div>
        </div>
        <div class="widget">
            <div class="widget-title">Veterinarians</div>
            <div class="widget-value">{{ $roleStats['vets'] }}</div>
        </div>
        <div class="widget">
            <div class="widget-title">Staff</div>
            <div class="widget-value">{{ $roleStats['staff'] }}</div>
        </div>
        <div class="widget">
            <div class="widget-title">Super Admins</div>
            <div class="widget-value">{{ $roleStats['super_admins'] }}</div>
        </div>
    </div>

    <div class="card">
        <h2 style="margin-top: 0;">All Users</h2>
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="border-bottom: 2px solid #e5e7eb; background: #f8fafc;">
                        <th style="padding: 0.85rem; text-align: left;">Name</th>
                        <th style="padding: 0.85rem; text-align: left;">Email</th>
                        <th style="padding: 0.85rem; text-align: left;">Role</th>
                        <th style="padding: 0.85rem; text-align: left;">Status</th>
                        <th style="padding: 0.85rem; text-align: left;">Joined</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr style="border-bottom: 1px solid #e5e7eb;">
                            <td style="padding: 0.85rem;">
                                {{ $user->name }}
                                @if ($user->isSuperAdmin())
                                    <span style="margin-left: 0.5rem; padding: 0.2rem 0.5rem; border-radius: 999px; background: #ede9fe; color: #5b21b6; font-size: 0.75rem; font-weight: 700;">
                                        SUPER ADMIN
                                    </span>
                                @endif
                            </td>
                            <td style="padding: 0.85rem;">{{ $user->email }}</td>
                            <td style="padding: 0.85rem;">{{ $user->role }}</td>
                            <td style="padding: 0.85rem;">{{ $user->status ?? 'Active' }}</td>
                            <td style="padding: 0.85rem;">{{ $user->created_at?->format('M j, Y') ?? 'N/A' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="padding: 1.25rem; text-align: center; color: #6b7280;">
                                No users found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($users->hasPages())
            <div style="margin-top: 1rem;">
                {{ $users->links() }}
            </div>
        @endif
    </div>
@endsection
