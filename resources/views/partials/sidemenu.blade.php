topmenu.blade.php<div class="collapse navbar-collapse navbar-ex1-collapse">
    <ul class="nav navbar-nav side-navbar">
        <li>
            {!! Form::open(['url' => '/search', 'class' => 'search-form']) !!}
            @if(empty($devicename))
                {!! Form::text('devicename', null, ['class' => 'form-control', 'placeholder' => '&#xF002;']) !!}
            @else
                {!! Form::text('devicename', $devicename, ['class' => 'form-control', 'placeholder' => '&#xF002;']) !!}
            @endif
            {!! Form::close() !!}
        </li>
        <li class="active">
            <a href="/home">
                <i class="fa fa-fw fa-dashboard"></i>概览
            </a>
        </li>
        <li>
            <a href="/devices">
                <i class="fa fa-fw fa-server"></i>设备
            </a>
        </li>
        <li>
            <a href="/maps">
                <i class="fa fa-fw fa-bar-chart"></i>视图
            </a>
        </li>
        <li>
            <a href="/events">
                <i class="fa fa-fw fa-exclamation-triangle"></i>事件
            </a>
        </li>
    </ul>
</div>
