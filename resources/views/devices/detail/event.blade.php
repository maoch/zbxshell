@extends('layouts.main')

@section('content')
<div class="content-wrapper">
    <div class="container-fluid">

        <h3 class="page-header">
            <span class="pull-left">事件信息</span>
            <span class="pull-right">
                <small>
                    <a href="{{ url('/devices/monitor/show/' .$name . '/' . $hostid) }}">
                        <i class="fa fa-eye"></i>
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
                <i class="fa fa-exclamation-triangle"></i>{{$name or ''}}
            </li>
        </ol>

        <div class="row">
            <div class="col-xs-12">
                <nav>
                    <div class="pull-right">
                        {!! $datas->render() !!}
                    </div>
                </nav>
            </div>
        </div>

        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>时间</th>
                    <th>级别</th>
                    <th>事件</th>
                </tr>
            </thead>
            <tbody>
            @foreach ($datas as $data)
                <tr>
                    <td scope="row">{{$data['time']}}</td>
                    <td>
                        @if($data['state']<=2)
                            <i class="fa fa-exclamation-circle text-warning"></i>
                        @else
                            <i class="fa fa-times-circle text-danger"></i>
                        @endif
                    </td>
                    <td>{{$data['description']}}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
