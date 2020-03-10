@csrf
<div class="form-group{{ $errors->has('is_published') ? ' has-error' : ''}}">
    {!! Form::label('is_published', 'Status: ', ['class' => 'control-label']) !!}
    {!! Form::select('is_published', $statuses, $course->is_published ?? old('is_published'), ['class' => 'form-control select']) !!}
    {!! $errors->first('is_published', '<p class="help-block">:message</p>') !!}
</div>

<div class="form-group{{ $errors->has('locale') ? ' has-error' : ''}}">
    {!! Form::label('locale', 'Locale: ', ['class' => 'control-label']) !!}
    {!! Form::select('locale', config('app.locales'), $course->locale ?? old('locale'), ['class' => 'form-control select']) !!}
    {!! $errors->first('locale', '<p class="help-block">:message</p>') !!}
</div>

<div class="form-group{{ $errors->has('themes') ? ' has-error' : ''}}">
    {!! Form::label('themes', 'Themes: ', ['class' => 'control-label']) !!}
    {!! Form::select('themes[]', $themes, $courseThemes ?? old('themes'), ['class' => 'form-control select', 'multiple' => 'multiple']) !!}
    {!! $errors->first('themes', '<p class="help-block">:message</p>') !!}
</div>

<div class="form-group{{ $errors->has('authors') ? ' has-error' : ''}}">
    {!! Form::label('authors', 'Authors: ', ['class' => 'control-label']) !!}
    {!! Form::select('authors[]', $authors, $courseAuthors ?? old('authors'), ['class' => 'form-control select', 'multiple' => 'multiple']) !!}
    {!! $errors->first('authors', '<p class="help-block">:message</p>') !!}
</div>

<div class="form-group{{ $errors->has('title') ? ' has-error' : ''}}">
    {!! Form::label('title', 'Title: ', ['class' => 'control-label']) !!}
    {!! Form::text('title', $course->title ?? old('title'), ['class' => 'form-control', 'required' => 'required']) !!}
    {!! $errors->first('title', '<p class="help-block">:message</p>') !!}
</div>

<div class="form-group hideable {{ $errors->has('body_in') ? ' has-error' : ''}}" id="form_{{ 'body_in' }}">
    {!! Form::label('body_in', 'Body Inside of Course: ', ['class' => 'control-label']) !!}
    {!! Form::textarea('body_in', $course->body_in ?? old('body_in'), ['class' => 'form-control ckeditor']) !!}
    {!! $errors->first('body_in', '<p class="help-block">:message</p>') !!}
</div>

<div class="form-group hideable {{ $errors->has('body_out') ? ' has-error' : ''}}" id="form_{{ 'body_in' }}">
    {!! Form::label('body_out', 'Body Outside of Course: ', ['class' => 'control-label']) !!}
    {!! Form::textarea('body_out', $course->body_out ?? old('body_out'), ['class' => 'form-control ckeditor']) !!}
    {!! $errors->first('body_out', '<p class="help-block">:message</p>') !!}
</div>

<div class="form-group hideable {{ $errors->has('img_preview') ? ' has-error' : ''}}">
    {!! Form::label('img_preview', 'Preview Image: ', ['class' => 'control-label']) !!}
    {!! Form::file('img_preview', ['class' => 'form-control-file']) !!}
    {!! $errors->first('img_preview', '<p class="help-block">:message</p>') !!}
    @if (isset($course->img_preview))
        <a target="_blank" href="{{ config('filesystems.disks.cl_course.url') . $course->img_preview }}">Image Link</a>
    @endif
</div>

<div class="form-group hideable {{ $errors->has('video') ? ' has-error' : ''}}">
    {!! Form::label('video', 'Preview Video: ', ['class' => 'control-label']) !!}
    {!! Form::file('video', ['class' => 'form-control-file']) !!}
    {!! $errors->first('video', '<p class="help-block">:message</p>') !!}
    @if (isset($course->video))
        <a target="_blank" href="{{ config('filesystems.disks.cl_course.url') . $course->video }}">Video Link</a>
    @endif
</div>

<div class="form-group">
    {!! Form::submit('Submit', ['class' => 'btn btn-primary']) !!}
</div>


@section('js')
    <script src="//cdn.ckeditor.com/4.11.4/standard/ckeditor.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.7/js/select2.min.js"></script>
    <script type="text/javascript">
        CKEDITOR.replace('body_in', {
            customConfig : 'config.js',
            toolbar : 'simple'
        });

        CKEDITOR.replace('body_out', {
            customConfig : 'config.js',
            toolbar : 'simple'
        });

        $('.form-control.select').select2();
    </script>
@stop
