
    @extends('layouts.template')

    @section('title', 'Users')

    @section('sidebar')
    @parent
        <p>This is appended to the master sidebar.</p>
    @endsection

    @section('content')
    
        @component('components.welcome')
            @slot('title')
                Welcome (slot title)
            @endslot
            <strong>It's Laravel</strong> (From Components)
        @endcomponent

        <div class="alert alert-info">
            <ul>取得 API 的 Token (method = GET)
                <li>路由 http://127.0.0.1/laravel/blog/public/user/getApiToken</li>
                <li>參數 name、password</li>
            </ul>
            <ul>取得會員清單 (method = GET)
                <li>路由 http://127.0.0.1/laravel/blog/public/api/getAllMember</li>
                <li>參數 api_token</li>
            </ul>
        </div>

        <table class="table">
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>API Token</th>
                <th>API Token Expired</th>
                <th>Created At</th>
                <th>Status</th>
                <th>API Token</th>
                <th>Delete</th>
            </tr>
            @foreach ($users as $user)
            <tr>
                <td>{{ $user->id }}</td>
                <td>{{ $user->name }}</td>
                <td>{{ $user->email }}</td>
                <td>
                    @if ($user->deleted_at !== null)
                        <del>{{ $user->api_token }}</del>
                    @else
                        {{ $user->api_token }}
                    @endif
                </td>
                <td>
                    {{ $user->api_token_expired }}
                    @if($user->api_token_expired !== null && $user->api_token_expired < $now)
                        <span class="badge badge-warning">過期</span>
                    @endif
                </td>
                <td>{{ $user->created_at }}</td>
                <td>
                    @if ($user->status === 2)
                        <span class="badge badge-danger">Deleted</span>
                    @elseif ($user->status === 1)
                        <span class="badge badge-success">Normal</span>
                    @elseif  ($user->status === 0)
                        <span class="badge badge-dark">OFF</span>
                    @endif
                </td>
                <td>
                    @if ($user->status === 1)
                    <form method="POST" action="{{ route('user.setApiToken' , $user->id) }}">
                        {{ csrf_field() }}
                        <input type="hidden" name="action" value="set" />
                        <input type="submit" value="{{ trans('global.action.generate') }}" />
                    </form>
                    <form method="POST" action="{{ route('user.setApiToken' , $user->id) }}">
                        {{ csrf_field() }}
                        <input type="hidden" name="action" value="clean" />
                        <input type="submit" value="{{ trans('global.action.clean') }}" />
                    </form>
                    @endif
                </td>
                <td>
                    @if ($user->status === 1)
                    <form method="POST" action="{{ route('user.destroy' , $user->id) }}">
                        {{ csrf_field() }}
                        <input type="submit" value="{{ trans('global.action.delete') }}" />
                    </form>
                    @endif
                </td>
            </tr>
            @endforeach
        </table>
        {{ $users->links() }}
    @endsection