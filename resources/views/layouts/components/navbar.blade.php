<!-- Navbar -->
<nav class="main-header navbar navbar-expand" style="background-color: #212e1f;">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
        <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#" role="button" style="color: #ffffff;">
                <i class="fas fa-bars" style="color: #ffffff;"></i>
            </a>
        </li>
        
    </ul>

    <!-- Right navbar links -->
    <ul class="navbar-nav ml-auto">
        <li class="nav-item dropdown user-menu">
            <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown" style="color: #ffffff;">
                @if (Auth::user()->user_image)
                <img
                    src="{{ Auth::user()->user_image }}"
                    class="user-image img-circle elevation-2"
                    alt="User Image">
                @else
                <img
                    src="{{ asset('vendor/adminlte3/img/avatar.png') }}"
                    class="user-image img-circle elevation-2"
                    alt="User Image">
                @endif
                <span class="d-none d-md-inline">{{ Auth::user()->name }}</span>
            </a>
            <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                <!-- User image -->
                <li class="user-header bg-secondary">
                    @if (Auth::user()->user_image)
                    <img
                        src="{{ Auth::user()->user_image }}"
                        class="img-circle elevation-2"
                        alt="User Image">
                    @else
                    <img
                        src="{{ asset('vendor/adminlte3/img/avatar.png') }}"
                        class="img-circle elevation-2"
                        alt="User Image">
                    @endif

                    <p>
                        {{ Auth::user()->name }}
                        <small>Bergabung pada {{Auth::user()->created_at->format('M d, Y')}}</small>
                    </p>
                </li>

                <!-- Menu Footer-->
                <li class="user-footer">
                    <a href="{{ route('profile') }}" class="btn btn-default btn-flat" style="color: #000000;">Profile</a>
                    <a
                        class="btn btn-default btn-flat float-right"
                        href="{{ route('logout') }}"
                        onclick="event.preventDefault();
                        document.getElementById('logout-form').submit();" style="color: #ffffff;">

                        <form
                            id="logout-form"
                            action="{{ route('logout') }}"
                            method="POST"
                            style="display: none;">
                            @csrf
                        </form>
                        <i class="ni ni-user-run" style="color: #000000;"></i>
                        <span style="color: #000000;">Logout</span>
                    </a>
                </li>
            </ul>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-widget="fullscreen" href="#" role="button" style="color: #ffffff;">
                <i class="fas fa-expand-arrows-alt" style="color: #ffffff;"></i>
            </a>
        </li>
      
    </ul>
</nav>
<!-- /.navbar -->
