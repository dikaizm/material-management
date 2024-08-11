@extends('layouts.base_admin.base_auth')
@section('judul', 'Halaman Registrasi')
@section('content')
<div class="register-box">
    <div class="register-logo">
        <a href="#">
            <img src="{{ asset('vendor/adminlte3/img/logo1.png') }}" alt="logo" width="200">
    </div>

    <div class="card">
        <div class="card-body register-card-body">
            <p class="login-box-msg" style="color: green;">Registrasi Akun Baru</p>

            <form action="{{ route('register') }}" method="post">
                @csrf
                <div class="input-group mb-3">
                    <input
                        id="name"
                        type="text"
                        placeholder="Nama Lengkap"
                        class="form-control @error('name') is-invalid @enderror"
                        name="name"
                        value="{{ old('name') }}"
                        required="required"
                        autocomplete="name"
                        autofocus="autofocus">
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
                    <input
                        id="email"
                        placeholder="Email"
                        type="email"
                        class="form-control @error('email') is-invalid @enderror"
                        name="email"
                        value="{{ old('email') }}"
                        required="required"
                        autocomplete="email">
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-envelope"></span>
                        </div>
                    </div>
                    @error('email')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                    @enderror
                </div>
                <div class="input-group mb-3">
                    <input
                        id="posisi"
                        type="text"
                        placeholder="Posisi"
                        class="form-control @error('posisi') is-invalid @enderror"
                        name="posisi"
                        value="{{ old('posisi') }}"
                        required="required"
                        autocomplete="posisi">
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-briefcase"></span>
                        </div>
                    </div>
                    @error('posisi')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                    @enderror
                </div>
                <div class="input-group mb-3">
                    <input
                        id="password"
                        type="password"
                        placeholder="Kata Sandi"
                        class="form-control @error('password') is-invalid @enderror"
                        name="password"
                        required="required"
                        autocomplete="new-password">
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
                <div class="input-group mb-3">
                    <input
                        placeholder="Ketik ulang kata sandi"
                        id="password-confirm"
                        type="password"
                        class="form-control"
                        name="password_confirmation"
                        required="required"
                        autocomplete="new-password">
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-lock"></span>
                        </div>
                    </div>
                </div>
                <div class="row" >
                    <div class="col-8">
                        Sudah punya akun? <a href="{{ route('login') }}" class="text-center">Login</a>
                    </div>
                    <div class="col-4">
                        <button type="submit" class="btn btn-success btn-block">Registrasi</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
