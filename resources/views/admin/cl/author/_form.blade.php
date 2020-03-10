@csrf
<div class="form-group{{ $errors->has('firstname') ? ' has-error' : ''}}">
    {!! Form::label('firstname', 'Firstname: ', ['class' => 'control-label']) !!}
    {!! Form::text('firstname', $author->firstname ?? old('firstname'), ['class' => 'form-control', 'required' => 'required']) !!}
    {!! $errors->first('firstname', '<p class="help-block">:message</p>') !!}
</div>

<div class="form-group{{ $errors->has('lastname') ? ' has-error' : ''}}">
    {!! Form::label('lastname', 'Lastname: ', ['class' => 'control-label']) !!}
    {!! Form::text('lastname', $author->lastname ?? old('lastname'), ['class' => 'form-control', 'required' => 'required']) !!}
    {!! $errors->first('lastname', '<p class="help-block">:message</p>') !!}
</div>

<div class="form-group{{ $errors->has('middlename') ? ' has-error' : ''}}">
    {!! Form::label('middlename', 'Middlename: ', ['class' => 'control-label']) !!}
    {!! Form::text('middlename', $author->middlename ?? old('middlename'), ['class' => 'form-control']) !!}
    {!! $errors->first('middlename', '<p class="help-block">:message</p>') !!}
</div>

<div class="form-group hideable {{ $errors->has('about') ? ' has-error' : ''}}">
    {!! Form::label('about', 'About: ', ['class' => 'control-label']) !!}
    {!! Form::textarea('about', $author->about ?? old('about'), ['class' => 'form-control']) !!}
    {!! $errors->first('about', '<p class="help-block">:message</p>') !!}
</div>

<div class="form-group hideable {{ $errors->has('avatar') ? ' has-error' : ''}}">
    {!! Form::label('avatar', 'Avatar: ', ['class' => 'control-label']) !!}
    {!! Form::file('avatar', ['class' => 'form-control-file']) !!}
    {!! $errors->first('avatar', '<p class="help-block">:message</p>') !!}
    @if (isset($author->avatar))
        <a target="_blank" href="{{ config('filesystems.disks.cl_author.url') . $author->avatar }}">Image Link</a>
    @endif
</div>

<div class="form-group">
    {!! Form::submit('Submit', ['class' => 'btn btn-primary']) !!}
</div>
