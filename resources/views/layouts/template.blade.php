<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title')</title>
    <!-- bootstrap & fontawesome -->
    <link href="{{ URL::to('/') }}/bower_components/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ URL::to('/') }}/bower_components/font-awesome/css/font-awesome.min.css" type="text/css" rel="stylesheet">
  
    <!-- text fonts -->
    <link rel="stylesheet" href="{{ URL::to('/') }}/resources/assets/css/ace-fonts.css" />

    <!-- ace styles -->
    <link rel="stylesheet" href="{{ URL::to('/') }}/resources/css/ace.min.css" class="ace-main-stylesheet" id="main-ace-style">
    <link rel="stylesheet" href="{{ URL::to('/') }}/resources/css/ace-skins.min.css" class="ace-main-stylesheet">

    <!-- ace settings handler -->
    <script src="{{ URL::to('/') }}/resources/assets/js/ace-extra.js"></script>
    <!-- self defined css js -->
    @yield('css')
    @yield('js')
</head>
<body class="skin-1">
    @include('layouts/header')

    <div class="main-container" id="main-container" style="padding-bottom: 60px" >
        @include('layouts/sidebar')
        <div class="main-content">
            <div class="page-content">
                <div class="row">
                    <div class="col-xs-12">
                        @yield('content')
                    </div><!-- /.col -->
                </div><!-- /.row -->
            </div><!-- /.page-content -->
        </div><!-- /.main-content -->
        @include('layouts/footer')
    </div><!-- /.main-container -->

    <script src="{{ URL::to('/') }}/bower_components/jquery/dist/jquery.min.js"></script>
    <!-- bootstrap JS -->
    <script src="{{ URL::to('/') }}/resources/js/moment.js"></script>
    <script src="{{ URL::to('/') }}/bower_components/bootstrap/dist/js/bootstrap.min.js"></script>
    <!-- ace scripts -->
    <script src="{{ URL::to('/') }}/resources/js/ace-elements.min.js"></script>
    <script src="{{ URL::to('/') }}/resources/js/ace.min.js"></script>
    <script src="{{ URL::to('/') }}/resources/assets/js/jcanvas.min.js"></script>
</body>
</html>
