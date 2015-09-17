@if(Session::has('message'))
    <div class="alert alert-success" role="alert">{{ Session::get('message') }}</div>
@endif

@if(Session::has('error'))
    <div class="alert alert-danger" role="alert">{{ Session::get('error') }}</div>
@endif

@if($errors->any())
    <div class="alert alert-danger" role="alert">
        <ui>
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ui>
    </div>
@endif