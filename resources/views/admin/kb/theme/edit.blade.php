@extends('adminlte::page')

@section('title', 'Create Article Theme')

@section('content_header')
    <h1>Edit {{ $theme->name }}</h1>
@stop

@section('content')

    <div class="row">
        <div class="col-xs-12">
            <div class="box box-info">
                <div class="box-header">
                    <h3 class="box-title">Theme</h3>
                </div>

                <div class="box-body">
                    <a href="{{ route('admin.kb.theme.index') }}" title="Back">
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

                    {!! Form::open(['route' => ['admin.kb.theme.update', 'theme' => $theme], 'method'=>'PATCH']) !!}

                    <div class="form-group{{ $errors->has('name') ? ' has-error' : ''}}">
                        {!! Form::label('name', 'Name: ', ['class' => 'control-label']) !!}
                        {!! Form::text('name', old('name') ?? $theme->name, ['class' => 'form-control', 'required' => 'required']) !!}
                        {!! $errors->first('name', '<p class="help-block">:message</p>') !!}
                    </div>

                    <div class="form-group">
                        {!! Form::submit('Update', ['class' => 'btn btn-primary']) !!}
                    </div>

                    {!! Form::close() !!}

                </div>
            </div>
        </div>
    </div>

@stop
