@extends('layouts.app')

@section('title', 'Đăng nhập')

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
                    <h2 class="section-title text-uppercase text-center">Đăng nhập</h2>

                    <form action="{{ route('account.login.post') }}" method="post" class="form-group flex-wrap">
                        @csrf
                        <div class="col-lg-12 pb-3">
                            <label for="UserName">Tài khoản *</label>
                            <input class="form-control" id="UserName" name="UserName" type="text"
                                   placeholder="Nhập tên đăng nhập hoặc email" value="{{ old('UserName') }}" autocomplete="username">
                        </div>
                        <div class="col-lg-12 pb-3">
                            <label for="Password">Mật khẩu *</label>
                            <input class="form-control" id="Password" name="Password" type="password"
                                   placeholder="•••••••••••" autocomplete="current-password">
                        </div>
                        <div>
                            @include('partials.validation-summary')
                        </div>
                        <div class="col-lg-12">
                            <button type="submit" name="submit" class="btn btn-dark btn-rounded-none btn-lg w-100 text-uppercase fs-6">
                                Đăng nhập
                            </button>
                        </div>
                    </form>

                    <div class="justify-content-evenly d-flex">
                        <div class="py-3 text-center">
                            <a href="{{ route('account.register') }}">Đăng ký tài khoản? </a>
                        </div>
                        <div class="py-3 text-center">
                            <a href="{{ route('forgot.index') }}">Quên mật khẩu?</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
