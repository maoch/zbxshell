@extends('layouts.main')

@section('content')
    <div class="content-wrapper">
        <div class="container-fluid">

            <h3 class="page-header">概览</h3>

            <div class="row">

                <!-- Health Ratio -->
                <div class="col-md-3">
                    <div id="host-health" class="donut-chart">
                    </div>
                </div>

                <div class="col-md-9">
                    {!! $cistr !!}
                </div>
            </div>

            <div class="panel panel-default">
                {!! $imagestr !!}
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script type="text/javascript">
        Morris.Donut({
            element: "host-health",
            data: [
                {label: "严重", value: {{$health_arr['error']}}},
                {label: "警告", value: {{$health_arr['warning']}}},
                {label: "正常", value: {{$health_arr['normal']}}},
            ],
            colors: ["#D9534F", "#F0AD4E", "#5CB85C"],
            resize: true
        });

        $(document).ready(function () {
            $("image").hover(function (event) {
                var elementtype = $(event.target).attr('elementtype');
                var elementid = $(event.target).attr('elementid');
                //0 - host; 1 - map;  2 - trigger; 3 - host group; 4 - image.
                if (elementtype == '0' || elementtype == '1') {
                    $(this).css("cursor", "pointer");
                }
                $(this).click(function () {
                    if (elementtype == '0') {
                        window.location.href = "{{ url('/devices/configuration/show/') }}" + "/" + "\"\"/" + elementid;
                    } else if (elementtype == '1') {
                        window.location.href = "{{ url('/maps/') }}" + "/" + elementid;
                    }
                });
            }, function () {
                $(this).removeClass("cursor");
            });

        });
    </script>

@endsection
