@extends('adminlte::page')

@section('title', 'Create Article')

@section('content_header')
    <h1>Create Article</h1>
@stop

@section('content')

    <div class="row">
        <div class="col-xs-12">
            <div class="box box-info">
                <div class="box-header">
                    <h3 class="box-title">Article</h3>
                </div>

                <div class="box-body">
                    <a href="{{ route('admin.kb.article.index') }}" title="Back">
                        <button class="btn btn-warning btn-sm">
                            <i class="fa fa-arrow-left" aria-hidden="true"></i>
                            Back
                        </button>
                    </a>
                    <br/>
                    <br/>
                    @if ($errors->any())
                        <ul class="alert alert-danger">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    @endif

                    {!! Form::open(['route'=>'admin.kb.article.store', 'method'=>'POST', 'files'=>true]) !!}

                    @include('admin.kb.article._form')

                    {!! Form::close() !!}

                </div>
            </div>
        </div>
    </div>

@stop
