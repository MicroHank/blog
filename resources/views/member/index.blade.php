
    @extends('layouts.template')

    @section('title', '首頁')

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

        <a href="{{ route('member.create') }}">
            <input type="submit" class="btn btn-primary" value="{{ trans('sidebar.member.new') }}" />
        </a>
        <table class="table">
            <tr>
                <th>User ID</th>
                <th>Account</th>
                <th>Password</th>
                <th>Username</th>
                <th>Group</th>
                <th>Created At</th>
                <th>Edit</th>
                <th>Delete</th>
            </tr>
            @foreach ($member as $user)
            <tr>
                <td>{{ $user->user_id }}</td>
                <td>
                    <a href="{{ route('member.show' , $user->user_id) }}">{{ $user->account }}</a>
                </td>
                <td>{{ $user->password }}</td>
                <td>{{ $user->user_name }}</td>
                <td>{{ $user->group_name }}</td>
                <td>{{ $user->created_at }}</td>
                <td>
                    <a href="{{ route('member.edit' , $user->user_id) }}">
                        <input type="submit" class="btn btn-info" value="{{ trans('global.action.edit') }}" />
                    </a>
                </td>
                <td>
                    <form method="POST" action="{{ route('member.destroy' , $user->user_id) }}">
                        {{ csrf_field() }}
                        {{ method_field('DELETE') }}
                        <input type="submit" class="btn btn-danger" value="{{ trans('global.action.delete') }}" />
                    </form>
                </td>
            </tr>
            @endforeach
        </table>
        {{ $member->links() }}
    @endsection