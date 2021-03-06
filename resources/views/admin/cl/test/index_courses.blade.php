@extends('adminlte::page')

@section('title', 'Courses')

@section('content_header')
    <h1>
        Courses
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
                <br/>
            <br/>

            <div class="table-responsive">
                <table class="table table-bordered table-striped">

                    <thead>
                    <tr>
                        <th>ID</th><th>Title</th><th>Created At</th><th>Actions</th>
                    </tr>
                    </thead>

                    <tbody id="tbody">

                    @foreach($courses as $key => $item)
                        <tr style="cursor:pointer" onclick="document.location = '{{ route('admin.cl.test-lessons.index', ['course' => $item]) }}'">
                            <td>{{ $item->id }}</td>
                            <td><a href="{{ route('admin.cl.test-lessons.index', ['course' => $item]) }}">{{ $item->title }}</a></td>
                            <td>{{ $item->created_at }}</td>
                            <td>

                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>

                <div class="pagination">
                    {!! $courses->appends(['search' => request()->get('search')])->render('vendor.pagination.bootstrap-4') !!}
                </div>
            </div>
        </div>
    </div>
@stop


