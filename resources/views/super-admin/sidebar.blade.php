<a href="{{ route('super-admin.dashboard') }}" class="sidebar-item {{ request()->routeIs('super-admin.dashboard') ? 'active' : '' }}">
    Dashboard
</a>
<a href="{{ route('super-admin.users') }}" class="sidebar-item {{ request()->routeIs('super-admin.users') ? 'active' : '' }}">
    Users
</a>
<a href="{{ route('super-admin.analytics') }}" class="sidebar-item {{ request()->routeIs('super-admin.analytics') ? 'active' : '' }}">
    Analytics
</a>
