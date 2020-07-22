    <div class="alert alert-info">
        PDF Download
    </div>

    <label>Read Data From Table Users </label>

    <table class="table">
        <tr>
            <th>id</th>
            <th>name</th>
            <th>email</th>
            <th>created_at</th>
            <th>status</th>
            <th>deleted_at</th>
        </tr>
        @foreach ($users as $user)
        <tr>
            <td>{{ $user->id }}</td>
            <td>{{ $user->name }}</td>
            <td>{{ $user->email }}</td>
            <td>{{ $user->created_at }}</td>
            <td>{{ $user->status }}</td>
            <td>{{ $user->deleted_at }}</td>
        </tr>
        @endforeach
    </table>