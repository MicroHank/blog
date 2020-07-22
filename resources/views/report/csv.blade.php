
    @extends('layouts.template')

    @section('title', 'CSV')

    @section('sidebar')
    @parent
        <p>This is appended to the master sidebar.</p>
    @endsection

    @section('content')

        <div class="alert alert-info">
            Test
        </div>

        <p>
            <a href="{{ route('report.downloadCSV') }}">
                <input type="submit" class="btn btn-primary" value="{{ trans('global.action.download') }} From Table users" />
            </a>
        </p>

        <label>Read Data From {{ base_path('public/csv/reader.csv') }}</label>
        <table class="table">
            <tr>
                <th>Name</th>
                <th>Age</th>
                <th>Skill</th>
            </tr>
            @foreach ($data as $d)
            <tr>
                <td>{{ $d[0] }}</td>
                <td>{{ $d[1] }}</td>
                <td>{{ $d[2] }}</td>
            </tr>
            @endforeach
        </table>
        
    @endsection