<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">
    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="{{ route('home') }}">
        <div class="sidebar-brand-icon rotate-n-15">
            <i class="fas fa-user-shield"></i>
        </div>
        <div class="sidebar-brand-text mx-3">Admin Panel</div>
    </a>

    <hr class="sidebar-divider my-0">

    <li class="nav-item {{ request()->routeIs('home') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('home') }}" @if (request()->routeIs('home')) aria-current="page" @endif>
            <i class="fas fa-fw fa-home"></i>
            <span>Beranda</span>
        </a>
    </li>

    <hr class="sidebar-divider">
    <div class="sidebar-heading">Manajemen</div>

    <li class="nav-item {{ request()->routeIs('users.*') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('users.index') }}"
            @if (request()->routeIs('users.*')) aria-current="page" @endif>
            <i class="fas fa-fw fa-users"></i>
            <span>Pengguna</span>
        </a>
    </li>

    <li class="nav-item {{ request()->routeIs('products.*') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('products.index') }}"
            @if (request()->routeIs('products.*')) aria-current="page" @endif>
            <i class="fas fa-fw fa-box-open"></i>
            <span>Produk</span>
        </a>
    </li>

    <li class="nav-item {{ request()->routeIs('customers.*') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('customers.index') }}"
            @if (request()->routeIs('customers.*')) aria-current="page" @endif>
            <i class="fas fa-fw fa-address-book"></i>
            <span>Pelanggan</span>
        </a>
    </li>

    <hr class="sidebar-divider">
    <div class="sidebar-heading">Penjualan</div>

    <li class="nav-item {{ request()->routeIs('transactions.*') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('transactions.index') }}"
            @if (request()->routeIs('transactions.*')) aria-current="page" @endif>
            <i class="fas fa-fw fa-shopping-cart"></i>
            <span>Transaksi</span>
        </a>
    </li>

    <hr class="sidebar-divider d-none d-md-block">
    <div class="text-center d-none d-md-inline">
        <button class="rounded-circle border-0" id="sidebarToggle" aria-label="Toggle sidebar"></button>
    </div>
</ul>
