
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
            <p>
                <form method="POST" action="{{ route('line.send') }}">
                    {{ csrf_field() }}
                    <label>送出訊息至 Line Notify 帳號</label>
                    <select name='user_id'>
                        @foreach ($line_notify as $line)
                            <option value='{{$line->user_id}}'>{{$line->name}}</option>
                        @endforeach
                    </select>
                    <input type="textarea" name="message" />
                    <input type="submit" value="{{ trans('global.action.send') }}" />
                </form>
            </p>

            <table class="table">
                <tr>
                    <th>ID</th>
                    <th>User ID</th>
                    <th>Username</th>
                    <th>TargetType</th>
                    <th>Target</th>
                    <th>Revoke</th>
                </tr>
                @foreach ($line_notify as $line)
                <tr>
                    <td>{{ $line->id }}</td>
                    <td>{{ $line->user_id }}</td>
                    <td>{{ $line->name }}</td>
                    <td>{{ $line->target_type }}</td>
                    <td>{{ $line->target }}</td>
                    <td>
                        <form method="POST" action="">
                            {{ csrf_field() }}
                            {{ method_field('DELETE') }}
                            <input type="submit" value="{{ trans('global.action.revoke') }}" />
                        </form>
                    </td>
                </tr>
                @endforeach
            </table>
        </div>

        <script>
        function lineNotifyoAuth2() {
            var url = 'https://notify-bot.line.me/oauth/authorize?' ;
            url += 'response_type=code' ;
            url += '&scope=notify' ;
            url += '&response_mode=form_post' ;
            url += '&client_id=TJa8634yOQRVxLn90k7RPT' ;
            url += '&redirect_uri=http://127.0.0.1/laravel/blog/public/line/getCode' ;
            url += '&state=abcd-abcd';
            window.location.href = url ;
        }
        </script>
    @endsection