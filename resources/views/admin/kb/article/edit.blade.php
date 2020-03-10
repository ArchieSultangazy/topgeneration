@extends('adminlte::page')

@section('title', 'Edit Article')

@section('content_header')
    <h1>{{ $article->title }}</h1>
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

                    {!! Form::open(['route' => ['admin.kb.article.update', 'article' => $article], 'method'=>'PATCH', 'files'=>true]) !!}

                    @include('admin.kb.article._form')

                    {!! Form::close() !!}

                </div>
            </div>
        </div>
    </div>

@stop

@section('js')
    <script src="//cdn.ckeditor.com/4.11.4/standard/ckeditor.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.7/js/select2.min.js"></script>
    <script type="text/javascript">
        CKEDITOR.replace('body', {
            customConfig : 'config.js',
            toolbar : 'simple'
        });
        $('.form-control.select').select2();
    </script>
@stop