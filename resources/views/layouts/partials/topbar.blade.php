<nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3" aria-label="Toggle sidebar">
        <i class="fa fa-bars"></i>
    </button>
    <h1 class="h5 mb-0 text-gray-800">@yield('page-heading', 'Beranda')</h1>

    <ul class="navbar-nav ml-auto">
        <div class="topbar-divider d-none d-sm-block"></div>

        @auth
            <li class="nav-item dropdown no-arrow">
                <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                    data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <span class="mr-2 d-none d-lg-inline text-gray-600 small font-weight-bold">
                        {{ Auth::user()->name }}
                    </span>
                    <img class="img-profile rounded-circle" src="{{ asset('assets/img/undraw_profile.svg') }}" alt="Profil">
                </a>
                <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="userDropdown">
                    <div class="dropdown-header">
                        <i class="fas fa-user-circle mr-1"></i> Sesi Aktif
                    </div>
                    <div class="dropdown-item disabled text-muted small">
                        {{ Auth::user()->email }}
                    </div>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item text-danger" href="#" data-toggle="modal" data-target="#logoutModal">
                        <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-danger"></i>
                        Keluar (Logout)
                    </a>
                </div>
            </li>
        @else
            <li class="nav-item">
                <a class="nav-link text-primary font-weight-bold" href="{{ route('login') }}">
                    <i class="fas fa-sign-in-alt mr-1"></i> Masuk
                </a>
            </li>
        @endauth
    </ul>
</nav>
