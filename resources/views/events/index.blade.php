@extends('layouts.main')

@section('content')
    <div class="content-wrapper">
        <div class="container-fluid">

            <h3 class="page-header">事件</h3>

            <nav>
                <div class="pull-right">
                    {!! $datas->render() !!}
                </div>
            </nav>

            <table class="table table-striped table-hover">
                <thead>
                <tr>
                    <th>时间</th>
                    <th>名称</th>
                    <th>级别</th>
                    <th>事件</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($datas as $data)
                    <tr>
                        <td scope="row">{{$data['time']}}</td>
                        <td>
                            <a href="{{ url('/devices/monitor/show/'.$data['name'].'/' .$data['hostid'] ) }}">{{$data['name']}}</a>
                        </td>
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
