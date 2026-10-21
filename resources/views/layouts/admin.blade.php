<!DOCTYPE html>
<html lang="id">

<head>
    @include('layouts.partials.header')
</head>

<body id="page-top">
    <div id="wrapper">
        @include('layouts.partials.sidebar')

        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                @include('layouts.partials.topbar')

                <div class="container-fluid">
                    @foreach (['success' => 'check-circle', 'error' => 'exclamation-circle', 'warning' => 'exclamation-triangle', 'info' => 'info-circle'] as $type => $icon)
                        @if (session($type))
                            <div class="alert alert-{{ $type === 'error' ? 'danger' : $type }} alert-dismissible fade show"
                                role="alert">
                                <i class="fas fa-{{ $icon }} mr-1"></i> {{ session($type) }}
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        @endif
                    @endforeach

                    @yield('content')
                </div>
            </div>

            @include('layouts.partials.footer')
        </div>
    </div>

    <a class="scroll-to-top rounded" href="#page-top" aria-label="Kembali ke atas">
        <i class="fas fa-angle-up"></i>
    </a>

    @include('layouts.partials.scripts')
</body>

</html>
