<style>
    .app-side-nav {
        position: fixed;
        top: 0;
        left: 0;
        bottom: 0;
        width: 74px;
        z-index: 1030;
        background: var(--surface-card);
        border-right: 1px solid var(--border-soft);
        box-shadow: 0 4px 20px rgba(15, 23, 42, 0.06);
        transition: width 0.2s ease;
        overflow-x: hidden;
    }

    .app-side-nav:hover {
        width: 238px;
    }

    .side-nav-brand {
        height: 70px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-bottom: 1px solid var(--border-soft);
    }

    .side-nav-menu {
        padding: 0.75rem 0.65rem;
        display: flex;
        flex-direction: column;
        gap: 0.2rem;
    }

    .side-nav-link {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        text-decoration: none;
        border-radius: 10px;
        color: var(--text-main);
        padding: 0.58rem 0.68rem;
        white-space: nowrap;
    }

    .side-nav-link:hover {
        background: var(--brand-primary-soft);
        color: var(--brand-primary);
    }

    .side-nav-link.active {
        background: var(--brand-primary-soft);
        color: var(--brand-primary);
        font-weight: 600;
    }

    .side-nav-link i {
        font-size: 1rem;
        min-width: 1.1rem;
        text-align: center;
    }

    .side-nav-label {
        opacity: 0;
        transition: opacity 0.15s ease;
    }

    .app-side-nav:hover .side-nav-label {
        opacity: 1;
    }

    .side-nav-footer {
        margin-top: auto;
        padding: 0.75rem 0.65rem;
        border-top: 1px solid var(--border-soft);
    }

    .mobile-top-nav {
        background: var(--surface-card);
        border-bottom: 1px solid var(--border-soft);
    }
</style>

@php
    $navUser = auth()->user();
    $isAdmin = $navUser?->hasRole('admin');
    $isStaff = $navUser?->hasRole('staff');
    $isPharmacist = $navUser?->hasRole('pharmacist');
@endphp

<aside class="app-side-nav d-none d-lg-flex flex-column">
    <a href="{{ route('dashboard') }}" class="side-nav-brand">
        <x-application-logo class="d-inline-block align-text-top" style="height: 2.1rem; width: auto;" />
    </a>
    <div class="side-nav-menu">
        <a class="side-nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}"><i class="bi bi-speedometer2"></i><span class="side-nav-label">Dashboard</span></a>
        @if($isPharmacist)
            @can('create sales')
                <a class="side-nav-link {{ request()->routeIs('sales.create') ? 'active' : '' }}" href="{{ route('sales.create') }}"><i class="bi bi-lightning-charge"></i><span class="side-nav-label">Quick Release</span></a>
            @endcan
            @can('view sales')
                <a class="side-nav-link {{ request()->routeIs('sales.*') ? 'active' : '' }}" href="{{ route('sales.index') }}"><i class="bi bi-cart-check"></i><span class="side-nav-label">POS / Sales</span></a>
            @endcan
            @can('view patients')
                <a class="side-nav-link {{ request()->routeIs('patients.*') ? 'active' : '' }}" href="{{ route('patients.index') }}"><i class="bi bi-people"></i><span class="side-nav-label">Patients</span></a>
            @endcan
            @can('create stock requests')
                <a class="side-nav-link {{ request()->routeIs('stock-requests.*') ? 'active' : '' }}" href="{{ route('stock-requests.index') }}"><i class="bi bi-box-arrow-in-down"></i><span class="side-nav-label">Stock Requests</span></a>
            @endcan
            @can('view reports')
                <a class="side-nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}" href="{{ route('reports.patient-purchases') }}"><i class="bi bi-clipboard-data"></i><span class="side-nav-label">Patient Reports</span></a>
            @endcan
        @elseif($isStaff)
            @can('view products')
                <a class="side-nav-link {{ request()->routeIs('products.*') ? 'active' : '' }}" href="{{ route('products.index') }}"><i class="bi bi-capsule-pill"></i><span class="side-nav-label">Inventory</span></a>
            @endcan
            @can('create purchase orders')
                <a class="side-nav-link {{ request()->routeIs('purchase-orders.create') ? 'active' : '' }}" href="{{ route('purchase-orders.create') }}"><i class="bi bi-file-earmark-plus"></i><span class="side-nav-label">Create PO</span></a>
            @endcan
            @can('view purchase orders')
                <a class="side-nav-link {{ request()->routeIs('purchase-orders.*') ? 'active' : '' }}" href="{{ route('purchase-orders.index') }}"><i class="bi bi-receipt"></i><span class="side-nav-label">Purchase Orders</span></a>
            @endcan
            @can('view incoming deliveries')
                <a class="side-nav-link {{ request()->routeIs('purchase-orders.incoming') ? 'active' : '' }}" href="{{ route('purchase-orders.incoming') }}"><i class="bi bi-box-seam"></i><span class="side-nav-label">Incoming</span></a>
            @endcan
            @can('approve stock release')
                <a class="side-nav-link {{ request()->routeIs('stock-requests.*') ? 'active' : '' }}" href="{{ route('stock-requests.index') }}"><i class="bi bi-check2-square"></i><span class="side-nav-label">Approve Release</span></a>
            @endcan
            @can('view reports')
                <a class="side-nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}" href="{{ route('reports.inventory') }}"><i class="bi bi-bar-chart"></i><span class="side-nav-label">Reports</span></a>
            @endcan
        @else
            @can('view products')
                <a class="side-nav-link {{ request()->routeIs('products.*') ? 'active' : '' }}" href="{{ route('products.index') }}"><i class="bi bi-capsule-pill"></i><span class="side-nav-label">Products</span></a>
            @endcan
            @can('view patients')
                <a class="side-nav-link {{ request()->routeIs('patients.*') ? 'active' : '' }}" href="{{ route('patients.index') }}"><i class="bi bi-people"></i><span class="side-nav-label">Patients</span></a>
            @endcan
            @can('view purchase orders')
                <a class="side-nav-link {{ request()->routeIs('purchase-orders.*') ? 'active' : '' }}" href="{{ route('purchase-orders.index') }}"><i class="bi bi-receipt"></i><span class="side-nav-label">Purchase Orders</span></a>
            @endcan
            @can('view incoming deliveries')
                <a class="side-nav-link {{ request()->routeIs('purchase-orders.incoming') ? 'active' : '' }}" href="{{ route('purchase-orders.incoming') }}"><i class="bi bi-box-seam"></i><span class="side-nav-label">Incoming</span></a>
            @endcan
            @can('view stock movements')
                <a class="side-nav-link {{ request()->routeIs('stock-movements.*') ? 'active' : '' }}" href="{{ route('stock-movements.index') }}"><i class="bi bi-arrow-left-right"></i><span class="side-nav-label">Stock Moves</span></a>
            @endcan
            @can('view reports')
                <a class="side-nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}" href="{{ route('reports.inventory') }}"><i class="bi bi-bar-chart"></i><span class="side-nav-label">Reports</span></a>
            @endcan
            @can('view audit logs')
                <a class="side-nav-link {{ request()->routeIs('audit-logs.*') ? 'active' : '' }}" href="{{ route('audit-logs.index') }}"><i class="bi bi-journal-text"></i><span class="side-nav-label">Audit Logs</span></a>
            @endcan
        @endif
    </div>
    <div class="side-nav-footer">
        <a class="side-nav-link {{ request()->routeIs('profile.edit') ? 'active' : '' }}" href="{{ route('profile.edit') }}"><i class="bi bi-person-circle"></i><span class="side-nav-label">Profile</span></a>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="side-nav-link border-0 bg-transparent w-100"><i class="bi bi-box-arrow-left"></i><span class="side-nav-label">Log Out</span></button>
        </form>
    </div>
</aside>

<nav class="navbar navbar-expand-lg mobile-top-nav d-lg-none">
    <div class="container-fluid">
        <a class="navbar-brand" href="{{ route('dashboard') }}">
            <x-application-logo class="d-inline-block align-text-top" style="height: 2rem; width: auto;" />
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mobileNav" aria-controls="mobileNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mobileNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">Dashboard</a></li>
                @if($isPharmacist)
                    @can('view sales')
                        <li class="nav-item"><a class="nav-link {{ request()->routeIs('sales.*') ? 'active' : '' }}" href="{{ route('sales.index') }}">Sales</a></li>
                    @endcan
                    @can('view patients')
                        <li class="nav-item"><a class="nav-link {{ request()->routeIs('patients.*') ? 'active' : '' }}" href="{{ route('patients.index') }}">Patients</a></li>
                    @endcan
                @elseif($isStaff)
                    @can('view products')
                        <li class="nav-item"><a class="nav-link {{ request()->routeIs('products.*') ? 'active' : '' }}" href="{{ route('products.index') }}">Inventory</a></li>
                    @endcan
                    @can('view purchase orders')
                        <li class="nav-item"><a class="nav-link {{ request()->routeIs('purchase-orders.*') ? 'active' : '' }}" href="{{ route('purchase-orders.index') }}">POs</a></li>
                    @endcan
                @else
                    @can('view products')
                        <li class="nav-item"><a class="nav-link {{ request()->routeIs('products.*') ? 'active' : '' }}" href="{{ route('products.index') }}">Products</a></li>
                    @endcan
                    @can('view reports')
                        <li class="nav-item"><a class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}" href="{{ route('reports.inventory') }}">Reports</a></li>
                    @endcan
                @endif
            </ul>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-outline-danger btn-sm">Log Out</button>
            </form>
        </div>
    </div>
</nav>
