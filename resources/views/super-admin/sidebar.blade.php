<a href="{{ route('super-admin.dashboard') }}" class="sidebar-item {{ request()->routeIs('super-admin.dashboard') ? 'active' : '' }}">
    Dashboard
</a>
<a href="{{ route('super-admin.users') }}" class="sidebar-item {{ request()->routeIs('super-admin.users') ? 'active' : '' }}">
    Accounts
</a>
<a href="{{ route('super-admin.monitoring') }}" class="sidebar-item {{ request()->routeIs('super-admin.monitoring') ? 'active' : '' }}">
    Monitoring
</a>
<a href="{{ route('super-admin.reports') }}" class="sidebar-item {{ request()->routeIs('super-admin.reports') ? 'active' : '' }}">
    Reports
</a>
<a href="{{ route('super-admin.settings') }}" class="sidebar-item {{ request()->routeIs('super-admin.settings') ? 'active' : '' }}">
    Settings
</a>
<a href="{{ route('super-admin.analytics') }}" class="sidebar-item {{ request()->routeIs('super-admin.analytics') ? 'active' : '' }}">
    Analytics
</a>
<a href="{{ route('super-admin.adherence-tests.index') }}" class="sidebar-item {{ request()->routeIs('super-admin.adherence-tests.*') ? 'active' : '' }}">
    Adherence Testing
</a>
<a href="{{ route('super-admin.backup') }}" class="sidebar-item {{ request()->routeIs('super-admin.backup*') ? 'active' : '' }}">
    Connection and Backup
</a>
