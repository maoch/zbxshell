@extends("layouts.main")
@section("content")
    <div class="content-wrapper">
        <div class="container-fluid">

        <h3 class="page-header">视图</h3>
                {!! Form::open() !!}
                <div class="form-group">
                    {!! Form::select("map-name", $mapname, $selectedId, ["class" => "form-control", "id" => "map-selector"]) !!}
                </div>
                {!! Form::close() !!}
                {!! $imagestr !!}

        </div>
    </div>
@endsection
@section('scripts')
    <script type="text/javascript">
        $(document).ready(function () {
            $("#map-selector").change(function () {
                window.location.href = "{{ url('/maps/') }}" + "/" + $("#map-selector").val();
            });

            $("image").hover(function (event) {
                var elementtype = $(event.target).attr('elementtype');
                var elementid = $(event.target).attr('elementid');
                //0 - host; 1 - map;  2 - trigger; 3 - host group; 4 - image.
                if (elementtype == '0' || elementtype == '1') {
                    //设置鼠标经过时，变成小手
                    $(this).css("cursor", "pointer");
                }
                $(this).click(function () {
                    if (elementtype == '0') {
                        window.location.href = "{{ url('/devices/monitor/show/') }}" + "/" + "\"\"/" + elementid;
                    } else if (elementtype == '1') {
                        window.location.href = "{{ url('/maps/') }}" + "/" + elementid;
                    }
                });
            }, function () {
                //鼠标离开时，去除小手
                $(this).removeClass("cursor");
            });

        });
    </script>
@endsection

