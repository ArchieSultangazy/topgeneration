@extends('adminlte::master')

@section('adminlte_css')
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/plugins/iCheck/square/blue.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/css/auth.css') }}">
    @yield('css')
@stop

@section('body_class', 'login-page')

@section('body')
    <div class="login-box">
        <div class="login-logo">
            <a href="{{ url(config('adminlte.dashboard_url', '/')) }}">{!! config('adminlte.logo', '<b>Academy</b>Admin') !!}</a>
        </div>
        <!-- /.login-logo -->
        <div class="login-box-body">
            <p class="login-box-msg" style="font-size: 24px">You don't have enough rights to enter here.</p>
            <form action="{{ url(config('adminlte.logout_url', 'logout')) }}" method="post">
                {!! csrf_field() !!}
                <div class="row">
                    <div class="col-xs-12">
                        <button type="submit"
                                class="btn btn-primary btn-block btn-flat">Logout</button>
                    </div>
                    <!-- /.col -->
                </div>
            </form>
        </div>
        <!-- /.login-box-body -->
    </div><!-- /.login-box -->
@stop