
    @extends('layouts.template')

    @section('title', '檢視會員')

    @section('sidebar')
    @parent
        <p>This is appended to the master sidebar.</p>
    @endsection

    @section('content')
        <a href="{{ route('member.index') }}">
            <input type="submit" value="{{ trans('sidebar.member.list') }}" />
        </a>

        <form method="POST" action="{{ route('member.store') }}">
            <div class="form-group">
                <label>User Account: </label>
                {{ $member->account }}
            </div>

            <div class="form-group">
                <label>User Password: </label>
                {{ $member->password }}
            </div>

            <div class="form-group">
                <label>User Name: </label>
                {{ $member->user_name }}
            </div>

            <div class="form-group">
                <label>Supervisor ID: </label>
                {{ $member->supervisor_id }}
            </div>

            <div class="form-group">
                <label>Created At: </label>
                {{ $member->created_at }}
            </div>
        </form>
    @endsection