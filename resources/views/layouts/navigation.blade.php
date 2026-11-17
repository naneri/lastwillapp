<nav class="mt-2">
    <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="navigation" aria-label="Main navigation" data-accordion="false" id="navigation">
        <li class="nav-item">
            <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="nav-icon bi bi-speedometer2"></i>
                <p>{{ __('Dashboard') }}</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('record.edit') }}" class="nav-link {{ request()->routeIs('record.*') ? 'active' : '' }}">
                <i class="nav-icon bi bi-journal-text"></i>
                <p>{{ __('Record') }}</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('will-receivers.edit') }}" class="nav-link {{ request()->routeIs('will-receivers.*') ? 'active' : '' }}">
                <i class="nav-icon bi bi-people"></i>
                <p>{{ __('Trusted Contacts') }}</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('billing.show') }}" class="nav-link {{ request()->routeIs('billing.*') ? 'active' : '' }}">
                <i class="nav-icon bi bi-credit-card"></i>
                <p>{{ __('Plan & Billing') }}</p>
            </a>
        </li>
    </ul>
</nav>
