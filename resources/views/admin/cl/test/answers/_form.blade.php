@csrf
<input type="hidden" value="{{$question->id}}" name="question_id">
<div class="form-group hideable {{ $errors->has('ru_name') ? ' has-error' : ''}}">
    {!! Form::label('body', 'Title(RU): ', ['class' => 'control-label']) !!}
    {!! Form::textarea('ru_name', $answer->ru_name ?? old('ru_name'), ['class' => 'form-control']) !!}
    {!! $errors->first('ru_name', '<p class="help-block">:message</p>') !!}
</div>

<div class="form-group hideable {{ $errors->has('kk_name') ? ' has-error' : ''}}">
    {!! Form::label('body', 'Title(KZ): ', ['class' => 'control-label']) !!}
    {!! Form::textarea('kk_name', $answer->kk_name ?? old('kk_name'), ['class' => 'form-control']) !!}
    {!! $errors->first('kk_name', '<p class="help-block">:message</p>') !!}
</div>

<div class="form-group hideable {{ $errors->has('en_name') ? ' has-error' : ''}}">
    {!! Form::label('en_name', 'Title(EN): ', ['class' => 'control-label']) !!}
    {!! Form::textarea('en_name', $answer->en_name ?? old('en_name'), ['class' => 'form-control']) !!}
    {!! $errors->first('en_name', '<p class="help-block">:message</p>') !!}
</div>

<div class="form-group hideable {{ $errors->has('is_correct') ? ' has-error' : ''}}">
    {!! Form::label('is_correct', 'Correct answer? ', ['class' => 'control-label']) !!}
    <p>Yes</p>
    {!! Form::radio('is_correct', 1 , isset($answer) && $answer ? ($answer->is_correct == 1 ? true : false) : false) !!}
    <p>No</p>
    {!! Form::radio('is_correct', 0 , isset($answer) && $answer ? ($answer->is_correct == 0 ? true : false) : false) !!}
    {!! $errors->first('is_correct', '<p class="help-block">:message</p>') !!}
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
