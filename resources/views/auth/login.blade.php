@extends('app')

@section('navigation')
    <nav class="navbar navbar-inverse navbar-fixed-top">
        <div class="container-fluid">
            <div class="navbar-header">
                <a class="navbar-brand" href="/">RAIDMIRROR</a>
            </div>
        </div>
    </nav>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-xs-8 col-sm-6 col-md-4 col-xs-offset-2 col-sm-offset-3 col-md-offset-4">
                <div class="login-form">

                    @include('messages.alert')

                    {!! Form::open(['url' => '/login']) !!}
                    <div class="form-group">
                        {!! Form::label('name', '账号') !!}
                        {!! Form::text('name', null, ['class' => 'form-control']) !!}
                    </div>

                    <div class="form-group">
                        {!! Form::label('password', '密码') !!}
                        {!! Form::password('password', ['class' => 'form-control']) !!}
                    </div>

                    {!! Form::submit('登录', ['class' => 'form-control btn btn-success']) !!}

                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>
@endsection
