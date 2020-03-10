@extends('adminlte::page')

@section('title', 'Article Themes')

@section('content_header')
    <h1>
        Article Themes
    </h1>
    <form class="input-group" style="display: flex; margin-top: 15px">
        <input type="text" class="form-control" name="search" placeholder="Search..." value="{{ request()->get('search') }}">
        <span class="input-group-append">
            <button class="btn btn-secondary" type="submit">
                <i class="fa fa-search"></i>
            </button>
        </span>
    </form>
@stop

@section('content')

    <div class="row">

        <div class="col-xs-12">

            <a href="{{ route('admin.kb.theme.create') }}" class="btn btn-success btn-sm" title="Add New Book">
                <i class="fa fa-plus" aria-hidden="true"></i> Add New
            </a>

            <br/>
            <br/>

            <div class="table-responsive">
                <table class="table table-bordered table-striped">

                    <thead>
                    <tr>
                        <th>ID</th><th>Name</th><th>Actions</th>
                    </tr>
                    </thead>

                    <tbody id="tbody">

                    @foreach($themes as $key => $item)
                        <tr>
                            <td>{{ $item->id }}</td>
                            <td>{{ $item->name }}</td>
                            <td>
                                <a href="{{ route('admin.kb.theme.edit', ['theme' => $item]) }}" title="Edit Theme">
                                    <button class="btn btn-primary btn-sm">
                                        <i class="fa fa-pencil-square-o" aria-hidden="true"></i>
                                    </button>
                                </a>
                                <form action="{{ route('admin.kb.theme.destroy', ['theme' => $item]) }}"
                                      method="post" style="display:inline-flex"
                                      onsubmit="return confirm('Do you really want to delete this theme?');">
                                    <button class="btn btn-danger btn-sm" type="submit">
                                        <i class="fa fa-trash-o" aria-hidden="true"></i>
                                    </button>
                                    {!! method_field('delete') !!}
                                    {!! csrf_field() !!}
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>

                <div class="pagination">
                    {!! $themes->appends(['search' => request()->get('search')])->render('vendor.pagination.bootstrap-4') !!}
                </div>
            </div>
        </div>
    </div>
@stop
