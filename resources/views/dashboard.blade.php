
    @extends('layouts.template')

    @section('title', 'Dashboard')

    @section('sidebar')
    @parent
        <p>This is appended to the master sidebar.</p>
    @endsection

    @section('content')
        @component('components.welcome')
            @slot('title')
                Welcome (slot title)
            @endslot
            <strong>It's Dashboard</strong>
        @endcomponent

        @if (session('message'))
        <div class="alert alert-info">
            {{ session('message') }}
        </div>
        @endif
        <p>
            <form method="POST" action="{{ route('seeder') }}">
                <input type="hidden" name="class" value="UserTableSeeder">
                <input type="submit" class="btn btn-danger" value="{{ trans('global.seeder.user') }}" />
                {{ csrf_field() }}
            </form>
        </p>

        <p>
            <form method="POST" action="{{ route('seeder') }}">
                <input type="hidden" name="class" value="MemberTableSeeder">
                <input type="submit" class="btn btn-primary" value="{{ trans('global.seeder.member') }}" />
                {{ csrf_field() }}
            </form>
        </p>

        <p>
            <form method="POST" action="{{ route('seeder') }}">
                <input type="hidden" name="class" value="GroupsTableSeeder">
                <input type="submit" class="btn btn-success" value="{{ trans('global.seeder.group') }}" />
                {{ csrf_field() }}
            </form>
        </p>

        <p>
            <form method="POST" action="{{ route('seeder') }}">
                <input type="hidden" name="class" value="UserGroupTableSeeder">
                <input type="submit" class="btn btn-info" value="{{ trans('global.seeder.user_group') }}" />
                {{ csrf_field() }}
            </form>
        </p>
            
        </a>

    @endsection