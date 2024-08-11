@extends('layouts.base_admin.base_auth') @section('judul', 'Halaman Login') @section('content')
<div class="login-box">
    <div class="login-logo">
        <a href="#">
            <img src="{{ asset('vendor/adminlte3/img/logo1.png') }}" alt="logo" width="200">
    </div>
    <!-- /.login-logo -->
    <div class="card mb-5">
        <div class="card-body login-card-body">
            <p class="login-box-msg" style="color: green;">Masuk untuk memulai sesi Anda</p>

            <form action="{{ route('login') }}" method="post">
                @csrf
                <div class="input-group mb-3">
                    <input
                        id="name"
                        type="name"
                        placeholder="Name"
                        class="form-control @error('name') is-invalid @enderror"
                        name="name"
                        value="{{ old('name') }}"
                        required="required"
                        autocomplete="name"
                        autofocus="autofocus">
                    {{-- <input type="email" class="form-control" placeholder="Email" autocomplete="off"> --}}
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-user"></span>
                        </div>
                    </div>
                    @error('name')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                    @enderror
                </div>
                <div class="input-group mb-3">
                    {{-- <input type="password" class="form-control" placeholder="Password"> --}}
                    <input
                        id="password"
                        type="password"
                        placeholder="Password"
                        class="form-control @error('password') is-invalid @enderror"
                        name="password"
                        required="required"
                        autocomplete="current-password">
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-lock"></span>
                        </div>
                    </div>
                    @error('password')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                    @enderror
                </div>
                <div class="row">
                    <div class="col-8">
                        <div class="icheck-primary">
                            <input type="checkbox" id="remember">
                            <label for="remember" style="color: green;">
                                Ingat sesi saya
                            </label>
                        </div>
                    </div>
                    <!-- /.col -->
                    <div class="col-4">
                        <button type="submit" class="btn btn-success btn-block">Masuk</button>
                    </div>
                    <!-- /.col -->
                </div>
            </form>

            {{-- <div class="social-auth-links text-center mb-3">
                <p>- OR -</p>
                <a href="#" class="btn btn-block btn-primary">
                    <i class="fab fa-facebook mr-2"></i>
                    Sign in using Facebook
                </a>
                <a href="#" class="btn btn-block btn-danger">
                    <i class="fab fa-google-plus mr-2"></i>
                    Sign in using Google+
                </a>
            </div> --}}
            <!-- /.social-auth-links -->

         
            {{-- <p class="mb-0-mt-10 text-dark">
                Belum mempunyai akun?
                <a href="{{ route('register') }}" class="text-center" >Register</a>
            </p>
        </div> --}}
        <!-- /.login-card-body -->
    </div>
</div>
@endsection

<!-- /.login-box -->
