@extends('layouts.main')

@section('content')
    <div class="content-wrapper">
        <div class="container-fluid">
            <h3 class="page-header">
                <span class="pull-left">历史数据</span>
                {!! Form::hidden('itemNameHid', str_replace('-|','/',$itemName)) !!}
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
                                <div id="placeholder"
                                     style="min-height: 250px;width: 100%;height: 100%; display: block;"></div>
                                <div id="placeholderMaster"
                                     style="min-height: 250px;width: 100%;height: 100%; display: block;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script type="text/javascript">
        $(function () {
            var data =  {{ $historyStr }};
            var detailOptions = {
                series: {lines: {show: true, lineWidth: 3}, shadowSize: 0},
                grid: {hoverable: true},
                xaxis: {mode: "time",timeformat: "%y-%m-%d"},
                selection: {mode: "x"}
            };
            var masterOptions = {
                series: {lines: {show: true, lineWidth: 3}, shadowSize: 0},
                grid: {hoverable: true},
                xaxis: {mode: "time",timeformat: "%y-%m-%d"},
                selection: {mode: "x"}
            };
            var dataDetail = [{label: "USD/oz", data: data}];

            var plotDetail = $.plot($("#placeholder"), dataDetail, detailOptions);
            var plotMaster = $.plot($("#placeholderMaster"), dataDetail, masterOptions);
            $("#placeholder").bind("plotselected", function (event, ranges) {
                plotDetail = $.plot($("#placeholder"), dataDetail, $.extend(true, {}, detailOptions, {
                    xaxis: {
                        min: ranges.xaxis.from,
                        max: ranges.xaxis.to
                    }
                }));
                plotMaster.setSelection(ranges, true);
            });
            $("#placeholderMaster").bind("plotselected", function (event, ranges) {
                plotDetail.setSelection(ranges);
            });
        });
    </script>
@endsection

