<ul class="nav navbar-nav navbar-right top-navbar">
    <li class="dropdown">
        @if(Session::has('username'))
            <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">
                <i class="fa fa-fw fa-user"></i>
                {{  Session::get('username') }}
                <span class="fa fa-fw fa-caret-down"></span>
            </a>
            <ul class="dropdown-menu" role="menu">
                <li>
                    <a href="{{URL('logout') }}">
                        <i class="fa fa-fw fa-sign-out"></i>安全退出
                    </a>
                </li>
            </ul>
        @endif
    </li>
</ul>
