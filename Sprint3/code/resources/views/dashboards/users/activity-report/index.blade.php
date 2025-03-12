@extends('dashboards.users.layouts.user-dash-layout')
@section('title','User Activities Report')
<script src="{{ asset('vendor/bootstrap/js/Chart.min.js') }}"></script>


@section('content')
<div class="container-fluid py-3">
  <h4 class="mb-3">User Activities Report</h4>
  <!-- Filter Form -->
  <form action="{{ route('user.activity-report') }}" method="GET" class="row g-3 mb-4">
    <div class="col-auto">
      <label for="role" class="col-form-label fw-bold">Filter by Role:</label>
    </div>
    <div class="col-auto">
      <select name="role" id="role" class="form-select">
        <option value="">-- All Roles --</option>
        <option value="admin"
          {{ request('role') == 'admin' ? 'selected' : '' }}>Admin
        </option>
        <option value="student"
          {{ request('role') == 'student' ? 'selected' : '' }}>Student
        </option>
        <option value="teacher"
          {{ request('role') == 'teacher' ? 'selected' : '' }}>Teacher
        </option>
        <option value="staff"
          {{ request('role') == 'staff' ? 'selected' : '' }}>Staff
        </option>
        <option value="headproject"
          {{ request('role') == 'headproject' ? 'selected' : '' }}>Head Project
        </option>
        <option value="guest"
          {{ request('role') == 'guest' ? 'selected' : '' }}>Guest
        </option>
      </select>
    </div>
    <div class="col-auto">
      <label for="date_filter" class="col-form-label fw-bold">Date Range:</label>
    </div>
    <div class="col-auto">
      <select name="date_filter" id="date_filter" class="form-select">
        <option value="">-- All Time --</option>
        <option value="daily" {{ request('date_filter')=='daily' ? 'selected' : '' }}>Today</option>
        <option value="weekly" {{ request('date_filter')=='weekly' ? 'selected' : '' }}>This Week</option>
        <option value="monthly" {{ request('date_filter')=='monthly' ? 'selected' : '' }}>This Month</option>
        <option value="custom" {{ request('date_filter')=='custom' ? 'selected' : '' }}>Custom Range</option>
      </select>
    </div>

    <!-- ถ้า custom: ใส่ start_date / end_date -->
    <div class="col-auto">
      <input type="date" name="start_date" class="form-control"
        value="{{ request('start_date') }}">
    </div>
    <div class="col-auto">
      <input type="date" name="end_date" class="form-control"
        value="{{ request('end_date') }}">
    </div>

    <div class="col-auto">
      <button type="submit" class="btn btn-primary">Filter</button>
    </div>
  </form>
  <!-- ส่วนบนของ index.blade.php -->
  <div class="d-flex justify-content-between mb-2">
    <h4>User Activities Report</h4>

  </div>

  <div class="row mb-3">
    <!-- Card แรก -->
    <div class="col-xl-3 col-md-6 mb-4">
      <div class="card border-left-primary shadow h-100 py-2">
        <div class="card-body">
          <div class="row no-gutters align-items-center">
            <div class="col mr-2">
              <!-- เปลี่ยนข้อความเป็น Login Count -->
              <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                Total Logins
                @if(request('role'))
                (Role: {{ request('role') }})
                @endif
              </div>
              <!-- ตรงนี้แสดงตัวเลข $loginCount -->
              <div class="h5 mb-0 font-weight-bold text-gray-800">
                {{ $loginCount }}
              </div>
            </div>
            <div class="col-auto">
              <i class="fas fa-user fa-2x text-gray-300"></i>
            </div>
          </div>
        </div>
      </div>
    </div>


    <div class="col-xl-3 col-md-6 mb-4">
      <div class="card border-left-info shadow h-100 py-2">
        <div class="card-body">
          <div class="row no-gutters align-items-center">
            <div class="col mr-2">
              <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                Login Failures
                @if(request('role'))
                (Role: {{ request('role') }})
                @endif
              </div>
              <div class="h5 mb-0 font-weight-bold text-gray-800">
                {{ $loginFailuresCount }}
              </div>
            </div>
            <div class="col-auto">
              <!-- เลือก icon ที่สื่อถึงการ fail -->
              <i class="fas fa-times-circle fa-2x text-gray-300"></i>
            </div>
          </div>
        </div>
      </div>
    </div>


    <div class="col-xl-2 col-md-6 mb-4">
      <div class="card border-left-success shadow h-100 py-2">
        <div class="card-body">
          <div class="row no-gutters align-items-center">
            <div class="col mr-2">
              <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                New Users
                @if(request('role'))
                (Role: {{ request('role') }})
                @endif
              </div>
              <div class="h5 mb-0 font-weight-bold text-gray-800">
                {{ $newUsersCount }}
              </div>
            </div>
            <div class="col-auto">
              <i class="fas fa-user-plus fa-2x text-gray-300"></i>
            </div>
          </div>
        </div>
      </div>
    </div>


    <div class="col-xl-2 col-md-6 mb-4">
      <div class="card border-left-warning shadow h-100 py-2">
        <div class="card-body">
          <div class="row no-gutters align-items-center">
            <div class="col mr-2">
              <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                Delete Users
                @if(request('role'))
                (Role: {{ request('role') }})
                @endif
              </div>
              <div class="h5 mb-0 font-weight-bold text-gray-800">
                {{ $deleteUsersCount }}
              </div>
            </div>
            <div class="col-auto">
              <!-- เลือก icon ให้สื่อว่าเป็นการลบ user -->
              <i class="fas fa-user-slash fa-2x text-gray-300"></i>
            </div>
          </div>
        </div>
      </div>
    </div>


    <div class="col-xl-2 col-md-6 mb-4">
      <div class="card border-left-secondary shadow h-100 py-2">
        <div class="card-body">
          <div class="row no-gutters align-items-center">
            <div class="col mr-2">
              <div class="text-xs font-weight-bold text-dark text-uppercase mb-1">
                Call Paper (Teacher)
              </div>
              <div class="h5 mb-0 font-weight-bold text-gray-800">
                {{ $callPaperCount }}
              </div>
            </div>
            <div class="col-auto">
              <!-- ไอคอนอะไรก็ได้ -->
              <i class="fas fa-file-alt fa-2x text-gray-300"></i>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- เริ่มต้นแถว (row) ใหม่ เพื่อวาง 2 การ์ดข้างกัน -->
    <div class="row mb-3">
      <!-- คอลัมน์ซ้าย: Logins per Month -->
      <div class="col-xl-8 col-lg-7">
        <div class="card shadow mb-4">
          <!-- Card Header - Dropdown -->
          <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <!-- เปลี่ยนชื่อหัวข้อได้ตามต้องการ -->
            <h6 class="m-0 font-weight-bold text-primary">
              {{ $chartTitle ?? 'Logins' }}
            </h6>


          </div>

          <!-- Card Body -->
          <div class="card-body">
            <div class="chart-area">
              <canvas id="myAreaChart"></canvas>
            </div>
          </div>
        </div>
      </div>
      <!-- จบคอลัมน์ซ้าย -->

      <!-- คอลัมน์ขวา: Top Programs หรือ Top Emails -->
      <div class="col-xl-4 col-lg-5">
        <div class="card shadow mb-4">
          <!-- Card Header -->
          <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">
              @if(request('top_usage') == 'email')
              Top 5 Active Emails (by Activity Count)
              @else
              Top Programs (Viewed by Guest)
              @endif
            </h6>

            <!-- Dropdown เลือกหมวด -->
            <div class="dropdown no-arrow">
              <a class="dropdown-toggle" href="#" role="button" id="topUsageMenuLink"
                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
              </a>
              <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in"
                aria-labelledby="topUsageMenuLink">
                <div class="dropdown-header">Choose Data</div>
                <a class="dropdown-item"
                  href="{{ route('user.activity-report', array_merge(request()->all(), ['top_usage' => 'program'])) }}">
                  Top Programs
                </a>
                <a class="dropdown-item"
                  href="{{ route('user.activity-report', array_merge(request()->all(), ['top_usage' => 'email'])) }}">
                  Top Emails
                </a>
              </div>
            </div>
            <!-- end dropdown -->
          </div>

          <!-- Card Body -->
          <div class="card-body">
            @if(request('top_usage') == 'email')
            @if(!empty($topEmails) && count($topEmails) > 0)
            <ul class="list-group">
              @foreach($topEmails as $email => $count)
              <li class="list-group-item d-flex justify-content-between align-items-center">
                <span>#{{ $loop->iteration }} – {{ $email }}</span>
                <span class="badge bg-primary rounded-pill">
                  {{ $count }} times
                </span>
              </li>
              @endforeach
            </ul>
            @else
            <p>No data found for email usage.</p>
            @endif
            @else
            @if(!empty($topPrograms) && count($topPrograms) > 0)
            <ul class="list-group">
              @foreach($topPrograms as $index => $tp)
              <li class="list-group-item d-flex justify-content-between align-items-center">
                <span>
                  #{{ $index + 1 }}
                  {{ $tp['program']->program_name_en }}
                  {{-- ลบ (ID={{ $tp['program']->id }}) ทิ้ง --}}
                </span>
                <span class="badge bg-primary rounded-pill">
                  {{ $tp['count'] }} views
                </span>
              </li>
              @endforeach
            </ul>

            @else
            <p class="mb-0">No data found for guest views.</p>
            @endif
            @endif
          </div>
        </div>
      </div>
      <!-- จบคอลัมน์ขวา -->
    </div>
    <!-- จบ row mb-3 -->

    <!-- ตัวอย่าง: วางฟอร์มสำหรับ Filter Action เหนือตาราง Activities Log -->
    <div class="mb-3">
      <form method="GET" action="{{ route('user.activity-report') }}" class="row g-3">
        <!-- ส่งค่า role/date_filter/start_date/end_date กลับไปด้วย เพื่อคงค่า filter เดิม -->
        <input type="hidden" name="role" value="{{ request('role') }}">
        <input type="hidden" name="date_filter" value="{{ request('date_filter') }}">
        <input type="hidden" name="start_date" value="{{ request('start_date') }}">
        <input type="hidden" name="end_date" value="{{ request('end_date') }}">

        <div class="col-auto">
          <label for="action_filter" class="col-form-label fw-bold">Filter by Action:</label>
        </div>
        <div class="col-auto">
          <select name="action_filter" id="action_filter" class="form-select">
            <option value="">-- All Actions --</option>
            @if(!empty($distinctActions))
            @foreach($distinctActions as $act)
            <option value="{{ $act }}"
              @if(request('action_filter')==$act) selected @endif>
              {{ $act }}
            </option>
            @endforeach
            @endif
          </select>
        </div>

        <div class="col-auto">
          <button type="submit" class="btn btn-primary">Filter Action</button>
        </div>
      </form>
    </div>



    <!-- Activities Table -->
    <div class="card">
      <div class="card-header">
        <h5 class="mb-0">Activities Log</h5>
      </div>
      <div class="card-body p-0">
        <table class="table table-hover mb-0">
          <thead class="table-light">
            <tr>
              <th>ID</th>
              <th>User</th>
              <th>Role</th>
              <th>Action</th>
              <th>Description</th>
              <th>Created At</th>
            </tr>
          </thead>
          <tbody>
            @forelse($activities as $act)
            <tr>
              <td>{{ $act->id }}</td>
              <td>{{ $act->user ? $act->user->email : 'N/A' }}</td>
              <td>{{ $act->role ?? 'N/A' }}</td>
              <td>{{ $act->action }}</td>
              <td>{{ $act->description }}</td>
              <td>{{ $act->created_at }}</td>
            </tr>
            @empty
            <tr>
              <td colspan="6" class="text-center py-3">
                No activities found.
              </td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
      @if($activities->hasPages())
      <div class="card-footer">
        {{ $activities->appends(request()->all())->links() }}
      </div>
      @endif
    </div>
  </div>
  <script>
    let lineLabels = @json($lineChartLabels);
    let lineData = @json($lineChartValues);
    let chartTitle = @json($chartTitle);


    var ctx = document.getElementById("myAreaChart").getContext('2d');
    var myLineChart = new Chart(ctx, {
      type: 'line',
      data: {
        labels: lineLabels,
        datasets: [{
          label: chartTitle,
          data: lineData,
          backgroundColor: "rgba(78,115,223,0.05)",
          borderColor: "rgba(78,115,223,1)",
          fill: true
        }]
      },
      options: {
        maintainAspectRatio: false,
        scales: {
          yAxes: [{
            ticks: {
              beginAtZero: true,
              stepSize: 1, // ขยับทีละ 1
              suggestedMax: 5 // ให้ max อยู่แถว 5
            }
          }]
        }
      }
    });
  </script>
  @endsection