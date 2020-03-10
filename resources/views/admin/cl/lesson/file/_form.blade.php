@csrf
<div class="form-group{{ $errors->has('lesson_id') ? ' has-error' : ''}}">
    {!! Form::label('lesson_id', 'Lesson: ', ['class' => 'control-label']) !!}
    {!! Form::select('lesson_id', $lessons, $lessonsFile->lesson_id ?? old('lesson_id'), ['class' => 'form-control select']) !!}
    {!! $errors->first('lesson_id', '<p class="help-block">:message</p>') !!}
</div>

<div class="form-group{{ $errors->has('title') ? ' has-error' : ''}}">
    {!! Form::label('title', 'Title: ', ['class' => 'control-label']) !!}
    {!! Form::text('title', $lessonsFile->title ?? old('title'), ['class' => 'form-control', 'required' => 'required']) !!}
    {!! $errors->first('title', '<p class="help-block">:message</p>') !!}
</div>

<div class="form-group hideable {{ $errors->has('body') ? ' has-error' : ''}}">
    {!! Form::label('body', 'Body: ', ['class' => 'control-label']) !!}
    {!! Form::textarea('body', $lessonsFile->body ?? old('body'), ['class' => 'form-control ckeditor']) !!}
    {!! $errors->first('body', '<p class="help-block">:message</p>') !!}
</div>

<div class="form-group hideable {{ $errors->has('link') ? ' has-error' : ''}}">
    {!! Form::label('link', 'File: ', ['class' => 'control-label']) !!}
    {!! Form::file('link', ['class' => 'form-control-file']) !!}
    {!! $errors->first('link', '<p class="help-block">:message</p>') !!}
    @if (isset($lessonsFile->link))
        <a target="_blank" href="{{ config('filesystems.disks.cl_lesson.url') . $lessonsFile->link }}">Link</a>
    @endif
</div>

<div class="form-group">
    {!! Form::submit('Submit', ['class' => 'btn btn-primary']) !!}
</div>


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
