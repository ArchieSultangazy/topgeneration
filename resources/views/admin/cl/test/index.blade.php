@extends('adminlte::page')

@section('title', 'Tests')

@section('content_header')
    <h1>
        Tests
    </h1>
@stop

@section('content')

    <div class="row">

        <div class="col-xs-12">

            <a href="{{ route('admin.cl.test.create', ['lesson' => $lesson]) }}" class="btn btn-success btn-sm" title="Add New Course">
                <i class="fa fa-plus" aria-hidden="true"></i> Add New
            </a>

            <br/>
            <br/>

            <div class="table-responsive">
                <table class="table table-bordered table-striped">

                    <thead>
                    <tr>
                        <th>ID</th><th>Created At</th><th>Actions</th>
                    </tr>
                    </thead>

                    <tbody id="tbody">

                    @foreach($tests as $key => $item)
                        <tr style="cursor: pointer">
                            <td onclick="window.location='{{route('admin.cl.questions.index', ['test' => $item])}}'">{{ $item->id }}</td>
                            <td onclick="window.location='{{route('admin.cl.questions.index', ['test' => $item])}}'">{{ $item->created_at }}</td>
                            <td>
                                <form action="{{ route('admin.cl.test.destroy', ['test' => $item]) }}"
                                      method="post" style="display:inline-flex"
                                      onsubmit="return confirm('Do you really want to delete this lesson?');">
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
                    {!! $tests->appends(['search' => request()->get('search')])->render('vendor.pagination.bootstrap-4') !!}
                </div>
            </div>
        </div>
    </div>
@stop
