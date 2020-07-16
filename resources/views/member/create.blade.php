
    @extends('layouts.template')

    @section('title', '新增會員')

    @section('sidebar')
    @parent
        <p>This is appended to the master sidebar.</p>
    @endsection

    @section('content')
        @if (session('status'))
        <div class="alert alert-success">
            {{ session('status') }}
        </div>
        @endif
        @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif
        <a href="{{ route('member.index') }}">
            <input type="submit" value="{{ trans('sidebar.member.list') }}" />
        </a>

        <form method="POST" action="{{ route('member.store') }}">
            {{ csrf_field() }}
            <div class="form-group">
                <label>User Account</label>
                <input type="text" name="account" value="{{ old('account') }}" />
            </div>

            <div class="form-group">
                <label>User Password</label>
                <input type="password" name="password1" />
            </div>

            <div class="form-group">
                <label>User Password (type again)</label>
                <input type="password" name="password2" />
            </div>

            <div class="form-group">
                <label>User Name</label>
                <input type="text" name="username" value="{{ old('username') }}" />
            </div>

            <input type="submit" value="{{ trans('global.action.add') }}" />
        </form>
    @endsection