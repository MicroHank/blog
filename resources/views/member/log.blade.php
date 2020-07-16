
    @extends('layouts.template')

    @section('title', 'Management Log')

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

        <a href="{{ route('member.index') }}">
            <input type="submit" value="{{ trans('sidebar.member.list') }}" />
        </a>
        <table class="table">
            <tr>
                <th>ID</th>
                <th>User ID</th>
                <th>Account</th>
                <th>Username</th>
                <th>Log</th>
                <th>Created_At</th>
            </tr>
            @foreach ($member_logs as $ml)
            <tr>
                <td>{{ $ml->id }}</td>
                <td>{{ $ml->user_id }}</td>
                <td>{{ $ml->account }}</td>
                <td>{{ $ml->user_name }}</td>
                <td>{{ $ml->log }}</td>
                <td>{{ $ml->created_at }}</td>
            </tr>
            @endforeach
        </table>

        {{ $member_logs->links() }}
        
    @endsection