
    @extends('layouts.template')

    @section('title', 'SMTP')

    @section('sidebar')
    @parent
        <p>This is appended to the master sidebar.</p>
    @endsection

    @section('content')

        <div class="alert alert-info">
            Send Mail By google SMTP
        </div>

        @if (session('status'))
        <div class="alert alert-success">
            {{ session('status') }}
        </div>
        @endif

        <form method="POST" action="{{ route('mail.send') }}">
            <p>
                <label>To mail:</label>
                <input type="text" name="to_mail" />
            </p>

            <input type="submit" class="btn btn-success" value="{{ trans('global.action.send') }}">
            {{ csrf_field() }}
        </form>
        
    @endsection