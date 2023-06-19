<!-- need to remove -->
<li class="nav-item">
    <a href="{{ route('dashboard') }}" class="nav-link {{ Request::is('admin') ? 'active' : '' }}">
        <i class="nav-icon fas fa-dashboard"></i>
        <p>Dashboard</p>
    </a>
</li>
