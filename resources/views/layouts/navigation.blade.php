<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom">
    <div class="container-fluid">
        <!-- Logo -->
        <a class="navbar-brand" href="{{ route('dashboard') }}">
            <x-application-logo class="d-inline-block align-text-top" style="height: 2.25rem; width: auto;" />
        </a>

        <!-- Hamburger Button -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <form method="POST" action="{{ route('logout') }}" class="ms-auto me-2">
            @csrf
            <button type="submit" class="btn btn-danger btn-sm">Log Out</button>
        </form>

        <!-- Navigation Links -->
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('patients.*') ? 'active' : '' }}" href="{{ route('patients.index') }}">{{ __('Patients') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('products.*') ? 'active' : '' }}" href="{{ route('products.index') }}">{{ __('Products') }}</a>
                </li>
                @can('view purchase orders')
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('purchase-orders.*') ? 'active' : '' }}" href="{{ route('purchase-orders.index') }}">POs</a>
                    </li>
                @endcan
                @can('view incoming deliveries')
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('purchase-orders.incoming') ? 'active' : '' }}" href="{{ route('purchase-orders.incoming') }}">Incoming</a>
                    </li>
                @endcan
                @canany(['create stock requests', 'approve stock release'])
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('stock-requests.*') ? 'active' : '' }}" href="{{ route('stock-requests.index') }}">Stock Requests</a>
                    </li>
                @endcanany
                @can('view reports')
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}" href="{{ route('reports.inventory') }}">Reports</a>
                    </li>
                @endcan
                @can('view stock movements')
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('stock-movements.*') ? 'active' : '' }}" href="{{ route('stock-movements.index') }}">Stock Moves</a>
                    </li>
                @endcan
                @can('view audit logs')
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('audit-logs.*') ? 'active' : '' }}" href="{{ route('audit-logs.index') }}">{{ __('Audit Logs') }}</a>
                    </li>
                @endcan
            </ul>

            <!-- User Dropdown -->
            <ul class="navbar-nav">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        {{ Auth::user()->name }}
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                        <li><a class="dropdown-item" href="{{ route('profile.edit') }}">{{ __('Profile') }}</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item">{{ __('Log Out') }}</button>
                            </form>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>
