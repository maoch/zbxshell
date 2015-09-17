@extends('layouts.main')

@section('content')
    <div class="content-wrapper">
        <div class="container-fluid">

            <h3 class="page-header">设备</h3>
            <nav>
                <div class="pull-right">
                    {!! $datas->appends(['devicename'=>$devicename])->render() !!}
                </div>
            </nav>
            <table class="table table-striped table-hover">
                <thead>
                <tr>
                    <th>名称</th>
                    <th>群组</th>
                    <th>状态</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($datas as $data)
                    <tr>
                        <td scope="row">
                            <a href="{{ url('/devices/monitor/show/' .$data['name'] .'/' .$data['hostid']) }}">{{$data['name']}}</a>
                        </td>
                        <td>
                            {{$data['groupname']}}
                        </td>
                        <td>
                            <a href="{{ url('/devices/event/show/' .$data['name'].'/' . $data['hostid']) }}">
                                {{-- 告警级别，危险：>=3,警告：=2,正常：0,1 --}}
                                @if($data['state']>=env('PRIORITY_AVERAGE'))
                                    <i class="fa fa-times-circle text-danger"></i>
                                @elseif($data['state']==env('PRIORITY_WARNING'))
                                    <i class="fa fa-exclamation-circle text-warning"></i>
                                @else
                                    <i class="fa fa-check-circle text-success"></i>
                                @endif
                            </a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
