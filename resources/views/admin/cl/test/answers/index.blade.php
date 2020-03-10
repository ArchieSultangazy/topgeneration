@extends('adminlte::page')

@section('title', 'Questions')

@section('content_header')
    <h1>
        Answers
    </h1>
@stop

@section('content')

    <div class="row">

        <div class="col-xs-12">

            <a href="{{ route('admin.cl.answers.create', ['question' => $question]) }}" class="btn btn-success btn-sm" title="Add New Course">
                <i class="fa fa-plus" aria-hidden="true"></i> Add New
            </a>

            <br/>
            <br/>

            <div class="table-responsive">
                <table class="table table-bordered table-striped">

                    <thead>
                    <tr>
                        <th>ID</th><th>Name</th><th>Created At</th><th>Actions</th>
                    </tr>
                    </thead>

                    <tbody id="tbody">
                    @foreach($answers as $key => $item)
                        <tr>
                            <td>{{ $item->id }}</td>
                            <td>{{ $item->ru_name }}</td>
                            <td>{{ $item->created_at }}</td>
                            <td>
                                <a href="{{ route('admin.cl.answers.edit', ['answer' => $item]) }}" title="Edit Course">
                                    <button class="btn btn-primary btn-sm">
                                        <i class="fa fa-pencil-square-o" aria-hidden="true"></i>
                                    </button>
                                </a>
                                <form action="{{ route('admin.cl.answers.destroy', ['answer' => $item]) }}"
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
                    {!! $answers->appends(['search' => request()->get('search')])->render('vendor.pagination.bootstrap-4') !!}
                </div>
            </div>
        </div>
    </div>
@stop
