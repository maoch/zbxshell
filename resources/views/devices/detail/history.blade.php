@extends('layouts.main')

@section('content')
    <!--suppress JSJQueryEfficiency -->
    <div class="content-wrapper">
        <div class="container-fluid">
            <h3 class="page-header">
                <span class="pull-left">历史数据</span>
            <span class="pull-right">
                <small>
                    <a href="{{ url('/devices/event/show/'. $name .'/'.$hostid) }}">
                        <i class="fa fa-exclamation-triangle"></i>
                    </a>
                </small>
            </span>
                <span class="clearfix"></span>
            </h3>

            <ol class="breadcrumb">
                <li>
                    <i class="fa fa-server"></i><a href="{{ url('/devices') }}">设备</a>
                </li>
                <li>
                    <i class="fa fa-eye"></i>
                    <a href="{{ url('/devices/monitor/show/'. $name .'/'.$hostid) }}">
                        {{$name}}
                    </a>
                </li>
                <li class="active">
                    <i class="fa fa-signal"></i>{{str_replace('-|','/',$itemName)}}
                </li>
            </ol>
            <div class="row">
                <div class="col-xs-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">周</div>
                        <div class="panel-body">
                            <div class="form-group">
                                <div id="placeholder"  style="min-height: 250px;width: 100%;height: 100%; display: block;"></div>
                            <div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script type="text/javascript">
        function createFlot(){
            var arr = {{$historyStr}}
                    $.plot($("#placeholder"),
                            [
                                {label: "History", data: arr}
                            ],
                            {
                                xaxis: {
                                    mode: "time",
                                    timeformat: "%y-%m-%d"
                                },
                                grid: {
                                    clickable: true,
                                    hoverable: true
                                }
                            }
                    );
        }
        $(function () {
            createFlot();
            //浏览器大小改变后，调用方法
            $(window).resize(function(){
                createFlot();
            });

        });
    </script>
@endsection

