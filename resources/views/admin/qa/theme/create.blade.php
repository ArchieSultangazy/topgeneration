@extends('adminlte::page')

@section('title', 'Create Question Theme')

@section('content_header')
    <h1>Create Question Theme</h1>
@stop

@section('content')

    <div class="row">
        <div class="col-xs-12">
            <div class="box box-info">
                <div class="box-header">
                    <h3 class="box-title">Theme</h3>
                </div>

                <div class="box-body">
                    <a href="{{ route('admin.qa.theme.index') }}" title="Back">
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

                    {!! Form::open(['route' => 'admin.qa.theme.store', 'method'=>'POST']) !!}

                        <div class="form-group{{ $errors->has('name') ? ' has-error' : ''}}">
                            {!! Form::label('name', 'Name: ', ['class' => 'control-label']) !!}
                            {!! Form::text('name', old('name'), ['class' => 'form-control', 'required' => 'required']) !!}
                            {!! $errors->first('name', '<p class="help-block">:message</p>') !!}
                        </div>

                        <div class="form-group">
                            {!! Form::submit('Create', ['class' => 'btn btn-primary']) !!}
                        </div>

                    {!! Form::close() !!}

                </div>
            </div>
        </div>
    </div>

@stop
