@extends('layouts.main')

@section('content')
    <div class="content-wrapper">
        <div class="container-fluid">
            <h3 class="page-header">
                <span class="pull-left">监控信息</span>
            <span class="pull-right">
                <small>
                    <a href="{{ url('/devices/event/show/' .$name . '/' .$hostid) }}">
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
                <li class="active">
                    <i class="fa fa-eye"></i>{{$name}}
                </li>
            </ol>
            <nav>
                <div class="pull-right">
                    {!! $datas->render() !!}
                </div>
            </nav>
            <table class="table  table-hover">
                <thead>
                <tr>
                    <th>名称</th>
                    <th>检测时间</th>
                    <th>检测数据</th>
                    <th>历史数据</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($datas as $key=>$detaildata)
                    <tr class="active">
                        <td scope="row" colspan="4" id="{{$key}}" class="groupname">
                            <span class="fa fa-angle-double-down"></span> {{$key}}
                        </td>
                    </tr>
                    @foreach($detaildata as $data)
                        <tr name="{{$key}}"  style="display: none">
                            <td scope="row">
                                {{$data['name']}}</td>
                            <td>{{$data['time']}}</td>
                            <td>{{$data['lastvalue']}}</td>
                            <td>
                                <a href="{{ url('/devices/history/show/' . $name . '/' . $hostid . '/' . $data['itemid'] . '/'. str_replace('/','-|',str_replace('%','%25',$data['name']))) }}">
                                    <i class="fa fa-signal"></i>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
@section('scripts')
    <script type="text/javascript">
        $(document).ready(function () {
            //点击获取当前ID，然后隐藏
            $('.groupname').each(function () {
                $(this).click(function(){
                    var groupclass = $(this).children().attr('class');
                    if(groupclass=='fa fa-angle-double-down'){
                        $(this).children().attr('class','fa fa-angle-double-up');
                    }else{
                        $(this).children().attr('class','fa fa-angle-double-down');
                    }
                    var groupid = $(this).attr('id');
                    $("[name$='"+groupid+"']").toggle();
                });
            });
        });

    </script>
@endsection
