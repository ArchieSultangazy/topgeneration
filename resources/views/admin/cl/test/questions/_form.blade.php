@csrf
<input type="hidden" value="{{$test->id}}" name="lesson_test_id">
<div class="form-group hideable {{ $errors->has('ru_name') ? ' has-error' : ''}}">
    {!! Form::label('body', 'Title(RU): ', ['class' => 'control-label']) !!}
    {!! Form::textarea('ru_name', $question->ru_name ?? old('ru_name'), ['class' => 'form-control']) !!}
    {!! $errors->first('ru_name', '<p class="help-block">:message</p>') !!}
</div>

<div class="form-group hideable {{ $errors->has('kk_name') ? ' has-error' : ''}}">
    {!! Form::label('body', 'Title(KZ): ', ['class' => 'control-label']) !!}
    {!! Form::textarea('kk_name', $question->kk_name ?? old('kk_name'), ['class' => 'form-control']) !!}
    {!! $errors->first('kk_name', '<p class="help-block">:message</p>') !!}
</div>

<div class="form-group hideable {{ $errors->has('en_name') ? ' has-error' : ''}}">
    {!! Form::label('en_name', 'Title(EN): ', ['class' => 'control-label']) !!}
    {!! Form::textarea('en_name', $question->en_name ?? old('en_name'), ['class' => 'form-control']) !!}
    {!! $errors->first('en_name', '<p class="help-block">:message</p>') !!}
</div>

<div class="form-group">
    {!! Form::submit('Submit', ['class' => 'btn btn-primary']) !!}
</div>


@section('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.7/js/select2.min.js"></script>
    <script type="text/javascript">
        $('.form-control.select').select2();
    </script>
@stop
