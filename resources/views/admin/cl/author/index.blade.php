@extends('adminlte::page')

@section('title', 'Authors')

@section('content_header')
    <h1>
        Authors
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

            <a href="{{ route('admin.cl.author.create') }}" class="btn btn-success btn-sm" title="Add New Author">
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

                    @foreach($authors as $key => $item)
                        <tr>
                            <td>{{ $item->id }}</td>
                            <td>{{ $item->firstname . " " . $item->lastname . " " . $item->middlename }}</td>
                            <td>
                                <a href="{{ route('admin.cl.author.edit', ['author' => $item]) }}" title="Edit Author">
                                    <button class="btn btn-primary btn-sm">
                                        <i class="fa fa-pencil-square-o" aria-hidden="true"></i>
                                    </button>
                                </a>
                                <form action="{{ route('admin.cl.author.destroy', ['author' => $item]) }}"
                                      method="post" style="display:inline-flex"
                                      onsubmit="return confirm('Do you really want to delete this author?');">
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
                    {!! $authors->appends(['search' => request()->get('search')])->render('vendor.pagination.bootstrap-4') !!}
                </div>
            </div>
        </div>
    </div>
@stop
