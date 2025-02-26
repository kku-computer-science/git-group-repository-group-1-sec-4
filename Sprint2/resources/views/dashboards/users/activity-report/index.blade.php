@extends('dashboards.users.layouts.user-dash-layout')
@section('title','User Activities Report')

@section('content')
<style>
  .table td {
    word-wrap: break-word;
    white-space: normal;
    max-width: 300px; /* ปรับขนาดได้ */
  }
</style>

  <div class="card">
    <div class="card-header">
      <h3>User Activities</h3>
    </div>
    <div class="card-body">
      <table class="table">
        <thead>
          <tr>
            <th>ID</th>
            <th>User</th>
            <th>Action</th>
            <th>Description</th>
            <th>Date</th>
          </tr>
        </thead>
        <tbody>
          @foreach($activities as $act)
            <tr>
              <td>{{ $act->id }}</td>
              <td>{{ optional($act->user)->getUserName() ?? 'N/A' }}</td>
              <td>{{ $act->action }}</td>
              <td>{{ $act->description }}</td>
              <td>{{ $act->created_at }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>
      {{ $activities->links() }}
    </div>
  </div>
@endsection
