@csrf
<div class="form-group{{ $errors->has('is_published') ? ' has-error' : ''}}">
    {!! Form::label('is_published', 'Status: ', ['class' => 'control-label']) !!}
    {!! Form::select('is_published', [0 => 'Не опубликованный', 1 => 'Опубликованный'], $article->is_published ?? old('is_published'), ['class' => 'form-control select']) !!}
    {!! $errors->first('is_published', '<p class="help-block">:message</p>') !!}
</div>

<div class="form-group{{ $errors->has('locale') ? ' has-error' : ''}}">
    {!! Form::label('locale', 'Locale: ', ['class' => 'control-label']) !!}
    {!! Form::select('locale', config('app.locales'), $article->locale ?? old('locale'), ['class' => 'form-control select']) !!}
    {!! $errors->first('locale', '<p class="help-block">:message</p>') !!}
</div>

<div class="form-group{{ $errors->has('type') ? ' has-error' : ''}}">
    {!! Form::label('type', 'Type: ', ['class' => 'control-label']) !!}
    {!! Form::select('type', $types, $article->type ?? old('type'), ['class' => 'form-control select', 'id' => 'type']) !!}
    {!! $errors->first('type', '<p class="help-block">:message</p>') !!}
</div>

<div class="form-group{{ $errors->has('themes') ? ' has-error' : ''}}">
    {!! Form::label('themes', 'Themes: ', ['class' => 'control-label']) !!}
    {!! Form::select('themes[]', $themes, $articleThemes ?? old('themes'), ['class' => 'form-control select', 'multiple' => 'multiple']) !!}
    {!! $errors->first('themes', '<p class="help-block">:message</p>') !!}
</div>

<div class="form-group{{ $errors->has('user_id') ? ' has-error' : ''}}">
    {!! Form::label('user_id', 'Author: ', ['class' => 'control-label']) !!}
    {!! Form::select('user_id', $authors, $article->user_id ?? old('user_id'), ['class' => 'form-control select']) !!}
    {!! $errors->first('user_id', '<p class="help-block">:message</p>') !!}
</div>

<div class="form-group{{ $errors->has('title') ? ' has-error' : ''}}">
    {!! Form::label('title', 'Title: ', ['class' => 'control-label']) !!}
    {!! Form::text('title', $article->title ?? old('title'), ['class' => 'form-control', 'required' => 'required']) !!}
    {!! $errors->first('title', '<p class="help-block">:message</p>') !!}
</div>

<div class="form-group {{ $errors->has('img_preview') ? ' has-error' : ''}}">
    {!! Form::label('img_preview', 'Preview Image: ', ['class' => 'control-label']) !!}
    {!! Form::file('img_preview', ['class' => 'form-control-file']) !!}
    {!! $errors->first('img_preview', '<p class="help-block">:message</p>') !!}
    @if (isset($article->img_preview))
        <a target="_blank" href="{{ config('filesystems.disks.kb_article.url') . $article->img_preview }}">Image Link</a>
    @endif
</div>

<div class="form-group hideable {{ $errors->has($article::TYPE_TEXT) ? ' has-error' : ''}}" id="form_{{ $article::TYPE_TEXT }}">
    {!! Form::label($article::TYPE_TEXT, 'Body: ', ['class' => 'control-label']) !!}
    {!! Form::textarea($article::TYPE_TEXT, $article->body ?? old($article::TYPE_TEXT), ['class' => 'form-control ckeditor']) !!}
    {!! $errors->first($article::TYPE_TEXT, '<p class="help-block">:message</p>') !!}
</div>

<div class="form-group hideable {{ $errors->has($article::TYPE_VIDEO_IN) ? ' has-error' : ''}}" id="form_{{ $article::TYPE_VIDEO_IN }}">
    {!! Form::label($article::TYPE_VIDEO_IN, 'Video: ', ['class' => 'control-label']) !!}
    {!! Form::file($article::TYPE_VIDEO_IN, ['class' => 'form-control-file']) !!}
    {!! $errors->first($article::TYPE_VIDEO_IN, '<p class="help-block">:message</p>') !!}
    @if (isset($article->video) && $article->type == $article::TYPE_VIDEO_IN)
        <a target="_blank" href="{{ config('filesystems.disks.kb_article.url') . $article->video }}">Current video link</a>
    @endif
</div>

<div class="form-group hideable {{ $errors->has($article::TYPE_VIDEO_OUT) ? ' has-error' : ''}}" id="form_{{ $article::TYPE_VIDEO_OUT }}">
    {!! Form::label($article::TYPE_VIDEO_OUT, 'Video: ', ['class' => 'control-label']) !!}
    {!! Form::text($article::TYPE_VIDEO_OUT,
        isset($article->video) && $article->type == $article::TYPE_VIDEO_OUT
            ? $article->video : old($article::TYPE_VIDEO_OUT),
        ['class' => 'form-control'])
    !!}
    {!! $errors->first($article::TYPE_VIDEO_OUT, '<p class="help-block">:message</p>') !!}
</div>

<div class="form-group">
    {!! Form::submit('Submit', ['class' => 'btn btn-primary']) !!}
</div>


@section('js')
    <script src="//cdn.ckeditor.com/4.11.4/standard/ckeditor.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.7/js/select2.min.js"></script>
    <script type="text/javascript">
        CKEDITOR.replace('{{ $article::TYPE_TEXT }}', {
            customConfig : 'config.js',
            toolbar : 'simple'
        });

        $('.form-control.select').select2();

        switchType();
        function switchType() {
            $(".hideable").hide();
            $("#form_" + $("#type").val()).show();
        }

        $("#type").on('select2:select', function(e) {
            switchType();
        });
    </script>
@stop
