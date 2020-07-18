
    @extends('layouts.template')

    @section('title', 'Line Notify Message')

    @section('sidebar')
    @parent
        <p>This is appended to the master sidebar.</p>
    @endsection

    @section('content')
        <div class="alert alert-info">
            測試 Notify API
        </div>

        @if (session('message'))
        <div class="alert alert-success">
            Message: {{ session('message') }}
        </div>
        @endif

        <div>
            <p>
                <input type="button" class="btn btn-primary" value="連結到 LineNotify 訂閱" onclick="lineNotifyoAuth2();" />
            </p>
            <p>
                <form method="POST" action="{{ route('line.checkAccessToken') }}">
                    {{ csrf_field() }}
                    <input type="submit" class="btn btn-info" value="Check Access Token" />
                </form>
            </p>
            <label>送出訊息至 Line Notify 帳號</label>
            <form method="POST" action="{{ route('line.send') }}">
                {{ csrf_field() }}
                <input type="text" name="message" />
                <input type="submit" value="{{ trans('global.action.send') }}" />
            </form>
        </div>

        <script>
        function lineNotifyoAuth2() {
            var URL = 'https://notify-bot.line.me/oauth/authorize?';
            URL += 'response_type=code';
            URL += '&scope=notify';
            URL += '&response_mode=form_post';
            URL += '&client_id=TJa8634yOQRVxLn90k7RPT';
            URL += '&redirect_uri=http://127.0.0.1/laravel/blog/public/line/getCode';
            URL += '&state=abcd-abcd';
            window.location.href = URL;
        }
        </script>
    @endsection