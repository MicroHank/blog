
    @extends('layouts.template')

    @section('title', '編輯會員')

    @section('sidebar')
    @parent
        <p>This is appended to the master sidebar.</p>
    @endsection

    @section('content')
        <a href="{{ route('member.index') }}">
            <input type="submit" value="{{ trans('sidebar.member.list') }}" />
        </a>

        <form method="POST" action="{{ route('member.update', $member->user_id) }}">
            {{ method_field('PUT') }}
            {{ csrf_field() }}
            <input type="hidden" name="user_id" value="{{ $member->user_id }}">
            
            <div class="form-group">
                <label>User Account</label>
                <input type="text" name="account" value="{{ $member->account }}" disabled="disabled" />
            </div>

            <div class="form-group">
                <label>User Password</label>
                <input type="password" name="password1" required="required" />
            </div>

            <div class="form-group">
                <label>User Password (type again)</label>
                <input type="password" name="password2" required="required" />
            </div>

            <div class="form-group">
                <label>User Name</label>
                <input type="text" name="username" value="{{ $member->user_name }}" required="required" />
            </div>

            <input type="submit" value="{{ trans('global.action.update') }}" />
        </form>
    @endsection