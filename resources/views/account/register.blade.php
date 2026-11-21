@extends('layouts.app')

@section('title', 'Đăng ký')

@section('styles')
    <style>
        body {
            margin: 0;
        }
    </style>
@endsection

@section('content')
    <section id="login-form" style="min-height: calc(100vh - 150px); display: flex; align-items: center; justify-content: center;">
        <div class="container-sm">
            <div class="row justify-content-center">
                <div class="col-lg-6" style="font-size: 16px">
                    <h2 class="section-title text-uppercase text-center">Đăng ký</h2>

                    <form action="{{ route('account.register.post') }}" method="post" class="form-group flex-wrap">
                        @csrf
                        <div class="col-lg-12 pb-3">
                            <label for="UserName">Tài khoản *</label>
                            <input class="form-control" id="UserName" name="UserName" type="text"
                                   placeholder="Nhập tên đăng nhập" value="{{ old('UserName') }}" autocomplete="username">
                        </div>
                        <div class="col-lg-12 pb-3">
                            <label for="Email">Email *</label>
                            <input class="form-control" id="Email" name="Email" type="email"
                                   placeholder="Nhập email" value="{{ old('Email') }}" autocomplete="email">
                        </div>
                        <div class="col-lg-12 pb-3">
                            <label for="Password">Mật khẩu *</label>
                            <input class="form-control" id="Password" name="Password" type="password"
                                   placeholder="•••••••••••" autocomplete="new-password">
                        </div>
                        <div>
                            @include('partials.validation-summary')
                        </div>
                        <div class="col-lg-12">
                            <button type="submit" class="btn btn-dark btn-rounded-none fs-6 btn-lg w-100 text-uppercase">Đăng ký
                            </button>
                        </div>
                    </form>

                    <div class="py-3 text-center">
                        <a href="{{ route('account.login') }}">Đã có tài khoản? </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
