@csrf
<div class="form-group{{ $errors->has('course_id') ? ' has-error' : ''}}">
    {!! Form::label('course_id', 'Course: ', ['class' => 'control-label']) !!}
    {!! Form::select('course_id', $courses, $lesson->course_id ?? old('course_id'), ['class' => 'form-control select']) !!}
    {!! $errors->first('course_id', '<p class="help-block">:message</p>') !!}
</div>

<div class="form-group{{ $errors->has('title') ? ' has-error' : ''}}">
    {!! Form::label('title', 'Title: ', ['class' => 'control-label']) !!}
    {!! Form::text('title', $lesson->title ?? old('title'), ['class' => 'form-control', 'required' => 'required']) !!}
    {!! $errors->first('title', '<p class="help-block">:message</p>') !!}
</div>

<div class="form-group hideable {{ $errors->has('body_short') ? ' has-error' : ''}}">
    {!! Form::label('body_short', 'Short Body: ', ['class' => 'control-label']) !!}
    {!! Form::textarea('body_short', $lesson->body_short ?? old('body_short'), ['class' => 'form-control ckeditor']) !!}
    {!! $errors->first('body_short', '<p class="help-block">:message</p>') !!}
</div>

<div class="form-group hideable {{ $errors->has('body') ? ' has-error' : ''}}">
    {!! Form::label('body', 'Body: ', ['class' => 'control-label']) !!}
    {!! Form::textarea('body', $lesson->body ?? old('body'), ['class' => 'form-control ckeditor']) !!}
    {!! $errors->first('body', '<p class="help-block">:message</p>') !!}
</div>

<div class="form-group hideable {{ $errors->has('img_preview') ? ' has-error' : ''}}">
    {!! Form::label('img_preview', 'Preview Image: ', ['class' => 'control-label']) !!}
    {!! Form::file('img_preview', ['class' => 'form-control-file']) !!}
    {!! $errors->first('img_preview', '<p class="help-block">:message</p>') !!}
    @if (isset($lesson->img_preview))
        <a target="_blank" href="{{ config('filesystems.disks.cl_lesson.url') . $lesson->img_preview }}">Image Link</a>
    @endif
</div>

<div class="form-group{{ $errors->has('video_type') ? ' has-error' : ''}}">
    {!! Form::label('video_type', 'Video type: ', ['class' => 'control-label']) !!}
    {!! Form::select('video_type', ['file' => 'File', 'url' => 'URL'], isset($lesson->video) ? (strpos($lesson->video, 'http') !== false ? 'url' : 'file') : old('type'), ['class' => 'form-control select', 'id' => 'video_type']) !!}
    {!! $errors->first('video_type', '<p class="help-block">:message</p>') !!}
</div>

<div id="video_place">
</div>
@if (isset($lesson->video))
    <a target="_blank" href="{{ (strpos($lesson->video, 'http') !== false ? "" : config("filesystems.disks.cl_lesson.url")) . $lesson->video }}">Video Link</a>
@endif

<div class="form-group{{ $errors->has('scheme') ? ' has-error' : ''}}">
    {!! Form::label('scheme', 'Scheme in JSON: ', ['class' => 'control-label']) !!}
    {!! Form::text('scheme', $lesson->scheme ?? old('scheme'), ['class' => 'form-control', 'required' => 'required']) !!}
    {!! $errors->first('scheme', '<p class="help-block">:message</p>') !!}
</div>

<div class="form-group{{ $errors->has('articles') ? ' has-error' : ''}}">
    {!! Form::label('articles', 'Articles: ', ['class' => 'control-label']) !!}
    {!! Form::select('articles[]', $articles, json_decode($lesson->articles ?? '') ?? old('articles'), ['class' => 'form-control select', 'multiple' => 'multiple']) !!}
    {!! $errors->first('articles', '<p class="help-block">:message</p>') !!}
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

        switchVideoDiv();
        function switchVideoDiv()
        {
            if ($("#video_type").val() == 'file') {
                $(".removable").remove();
                $("#video_place").append('<div class="form-group removable hideable {{ $errors->has("video_file") ? "has-error" : ""}}">' +
                    '    {!! Form::label("video_file", "Upload Video: ", ["class" => "control-label"]) !!}' +
                    '    {!! Form::file("video_file", ["class" => "form-control-file"]) !!}' +
                    '    {!! $errors->first("video_file", "<p class=\"help-block\">:message</p>") !!}' +
                    '</div>'
                );
            }
            if ($("#video_type").val() == 'url') {
                $(".removable").remove();
                $("#video_place").append('<div class="form-group removable {{ $errors->has("video_url") ? "has-error" : ""}}">' +
                    '    {!! Form::label("video_url", "URL of Video: ", ["class" => "control-label"]) !!}' +
                    '    {!! Form::text("video_url", $lesson->video ?? old("video_url"), ["class" => "form-control", "required" => "required"]) !!}' +
                    '    {!! $errors->first("video_url", "<p class=\"help-block\">:message</p>") !!}' +
                    '</div>'
                );
            }
        }

        $("#video_type").on('select2:select', function(e) {
            switchVideoDiv();
        });
    </script>
@stop
