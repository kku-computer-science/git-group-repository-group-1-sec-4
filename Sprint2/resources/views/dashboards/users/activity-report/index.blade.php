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
    <a href="{{ route('user.activity-report.export-pdf', request()->all()) }}"
      class="btn btn-sm btn-danger">
      Export PDF
    </a>
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

    <!-- Card #2: New Users -->
    <div class="col-xl-3 col-md-6 mb-4">
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

    <!-- Card #3: New Funds -->
    <div class="col-xl-3 col-md-6 mb-4">
      <div class="card border-left-info shadow h-100 py-2">
        <div class="card-body">
          <div class="row no-gutters align-items-center">
            <div class="col mr-2">
              <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                New Funds
                @if(request('role'))
                (Role: {{ request('role') }})
                @endif
              </div>
              <div class="h5 mb-0 font-weight-bold text-gray-800">
                {{ $newFundsCount }}
              </div>
            </div>
            <div class="col-auto">
              <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Card #4: New Research Projects -->
    <div class="col-xl-3 col-md-6 mb-4">
      <div class="card border-left-warning shadow h-100 py-2">
        <div class="card-body">
          <div class="row no-gutters align-items-center">
            <div class="col mr-2">
              <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                New Research Projects
                @if(request('role'))
                (Role: {{ request('role') }})
                @endif
              </div>
              <div class="h5 mb-0 font-weight-bold text-gray-800">
                {{ $newResearchProjectsCount }}
              </div>
            </div>
            <div class="col-auto">
              <i class="fas fa-file-alt fa-2x text-gray-300"></i>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="row">
    <!-- ส่วนของ Area Chart -->
    <div class="col-xl-8 col-lg-7">
      <div class="card shadow mb-4">
        <!-- Card Header - Dropdown -->
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
          <!-- เปลี่ยนชื่อหัวข้อได้ตามต้องการ -->
          <h6 class="m-0 font-weight-bold text-primary">
            Logins per Month ({{ $lineChartRole ?? 'Teacher' }})
          </h6>

          <!-- เพิ่ม Dropdown ตรงนี้ -->
          <div class="dropdown no-arrow">
            <a class="dropdown-toggle" href="#" role="button" id="lineChartMenuLink"
              data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
              <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
            </a>
            <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in"
              aria-labelledby="lineChartMenuLink">
              <div class="dropdown-header">Choose Role</div>
              <!-- ตัวอย่างรายการ Role ที่ให้ผู้ใช้เลือก -->
              <a class="dropdown-item"
                href="{{ route('user.activity-report', array_merge(request()->all(), ['line_filter' => 'teacher'])) }}">
                Teacher
              </a>
              <a class="dropdown-item"
                href="{{ route('user.activity-report', array_merge(request()->all(), ['line_filter' => 'student'])) }}">
                Student
              </a>
              <a class="dropdown-item"
                href="{{ route('user.activity-report', array_merge(request()->all(), ['line_filter' => 'admin'])) }}">
                Admin
              </a>
              <a class="dropdown-item"
                href="{{ route('user.activity-report', array_merge(request()->all(), ['line_filter' => 'staff'])) }}">
                Staff
              </a>
              <a class="dropdown-item"
                href="{{ route('user.activity-report', array_merge(request()->all(), ['line_filter' => 'headproject'])) }}">
                Head Project
              </a>
              <a class="dropdown-item"
                href="{{ route('user.activity-report', array_merge(request()->all(), ['line_filter' => 'guest'])) }}">
                Guest
              </a>
            </div>
          </div>
          <!-- end Dropdown -->
        </div>

        <!-- Card Body -->
        <div class="card-body">
          <div class="chart-area">
            <canvas id="myAreaChart"></canvas>
          </div>
        </div>
      </div>
    </div>

    <!-- Pie Chart -->
    <div class="col-xl-4 col-lg-5">
      <div class="card shadow mb-4">
        <!-- Card Header - Dropdown -->
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
          <h6 class="m-0 font-weight-bold text-primary">Create Activities Ratio</h6>

          <!-- เพิ่ม Dropdown ตรงนี้ -->
          <div class="dropdown no-arrow">
            <a class="dropdown-toggle" href="#" role="button" id="pieChartMenuLink"
              data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
              <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
            </a>
            <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in"
              aria-labelledby="pieChartMenuLink">
              <div class="dropdown-header">Choose Category</div>

              <!-- ตัวอย่าง: -->
              <a class="dropdown-item"
                href="{{ route('user.activity-report', array_merge(request()->all(), ['pie_filter' => 'auth_actions'])) }}">
                Auth Actions
              </a>
              <a class="dropdown-item"
                href="{{ route('user.activity-report', array_merge(request()->all(), ['pie_filter' => 'user_management'])) }}">
                User Management
              </a>
              <a class="dropdown-item"
                href="{{ route('user.activity-report', array_merge(request()->all(), ['pie_filter' => 'book_patent'])) }}">
                Book & Patent
              </a>
              <a class="dropdown-item"
                href="{{ route('user.activity-report', array_merge(request()->all(), ['pie_filter' => 'dept_program'])) }}">
                Department & Program
              </a>
              <a class="dropdown-item"
                href="{{ route('user.activity-report', array_merge(request()->all(), ['pie_filter' => 'fund'])) }}">
                Fund
              </a>
              <a class="dropdown-item"
                href="{{ route('user.activity-report', array_merge(request()->all(), ['pie_filter' => 'permission_role'])) }}">
                Permission & Role
              </a>
              <a class="dropdown-item"
                href="{{ route('user.activity-report', array_merge(request()->all(), ['pie_filter' => 'research'])) }}">
                Research
              </a>
              <a class="dropdown-item"
                href="{{ route('user.activity-report', array_merge(request()->all(), ['pie_filter' => 'file_stuff'])) }}">
                File Stuff
              </a>
              <div class="dropdown-divider"></div>
              <a class="dropdown-item"
                href="{{ route('user.activity-report', array_merge(request()->all(), ['pie_filter' => 'all_actions'])) }}">
                All Actions
              </a>
            </div>

          </div>
          <!-- end Dropdown -->

        </div>

        <div class="card-body">
          <div class="chart-pie pt-4 pb-2">
            <!-- Canvas Pie Chart -->
            <canvas id="myPieChart"></canvas>
          </div>
          <div class="mt-4 text-center small" id="pieChartLegend">
            <!-- ตรงนี้ถ้าต้องการ legend เพิ่มเติม หรือจะให้ Chart.js generate เองก็ได้ -->
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- ใส่ progress ต่อท้าย -->
  <div class="row">
    <div class="col-lg-6 mb-4">
      <div class="card shadow mb-4">
        <div class="card-header py-3">
          <h6 class="m-0 font-weight-bold text-primary">Activity Progress</h6>
        </div>
        <div class="card-body">

          <!-- ตัวอย่าง #1: create_user -->
          <h4 class="small font-weight-bold">
            Create User
            <span class="float-right">{{ $createUserPercent }}%</span>
          </h4>
          <div class="progress mb-4">
            <div class="progress-bar bg-danger" role="progressbar"
              style="width: {{ $createUserPercent }}%"
              aria-valuenow="{{ $createUserPercent }}" aria-valuemin="0" aria-valuemax="100">
            </div>
          </div>

          <!-- ตัวอย่าง #2: create_fund -->
          <h4 class="small font-weight-bold">
            Create Fund
            <span class="float-right">{{ $createFundPercent }}%</span>
          </h4>
          <div class="progress mb-4">
            <div class="progress-bar bg-warning" role="progressbar"
              style="width: {{ $createFundPercent }}%"
              aria-valuenow="{{ $createFundPercent }}" aria-valuemin="0" aria-valuemax="100">
            </div>
          </div>

          <!-- ตัวอย่าง #3: create_research_project -->
          <h4 class="small font-weight-bold">
            Create Research Project
            <span class="float-right">{{ $createResearchPercent }}%</span>
          </h4>
          <div class="progress mb-4">
            <div class="progress-bar bg-info" role="progressbar"
              style="width: {{ $createResearchPercent }}%"
              aria-valuenow="{{ $createResearchPercent }}" aria-valuemin="0" aria-valuemax="100">
            </div>
          </div>

          <!-- ตัวอย่าง #4: create_department -->
          <h4 class="small font-weight-bold">
            Create Department
            <span class="float-right">{{ $createDeptPercent }}%</span>
          </h4>
          <div class="progress mb-4">
            <div class="progress-bar bg-success" role="progressbar"
              style="width: {{ $createDeptPercent }}%"
              aria-valuenow="{{ $createDeptPercent }}" aria-valuemin="0" aria-valuemax="100">
            </div>
          </div>

        </div> <!-- end card-body -->
      </div> <!-- end card -->
    </div> <!-- end col-lg-6 -->
  </div> <!-- end row -->

  <!-- ด้านขวา: Blog / Ranking -->
  <div class="col-lg-6 mb-4">
    <div class="card shadow mb-4">
      <!-- Card Header -->
      <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">
          <!-- ถ้าตัวแปร $topUsage เป็น 'language' จะแสดง "Top Languages"
             ถ้าไม่ใช่ (หรือค่า default) จะแสดง "Top Programs (Viewed by Guest)" -->
          @if(request('top_usage') == 'language')
          Top Languages (Used by All/Guest)
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
              href="{{ route('user.activity-report', array_merge(request()->all(), ['top_usage' => 'language'])) }}">
              Top Languages
            </a>
          </div>
        </div>
        <!-- end dropdown -->
      </div>

      <!-- Card Body -->
      <div class="card-body">
        @if(request('top_usage') == 'language')
        {{-- แสดงส่วน "Top Languages" --}}
        @if(!empty($topLanguages) && count($topLanguages) > 0)
        <ul class="list-group">
          @foreach($topLanguages as $lang => $count)
          <li class="list-group-item d-flex justify-content-between align-items-center">
            <span>{{ $lang }}</span>
            <span class="badge bg-primary rounded-pill">
              {{ $count }} times
            </span>
          </li>
          @endforeach
        </ul>
        @else
        <p class="mb-0">No data found for language usage.</p>
        @endif
        @else
        {{-- แสดงส่วน "Top Programs" (ของเดิม) --}}
        @if(!empty($topPrograms) && count($topPrograms) > 0)
        <ul class="list-group">
          @foreach($topPrograms as $index => $tp)
          <li class="list-group-item d-flex justify-content-between align-items-center">
            <span>
              #{{ $index + 1 }}
              {{ $tp['program']->program_name_en }}
              (ID={{ $tp['program']->id }})
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
      {{ $activities->links() }}
    </div>
    @endif
  </div>
</div>
<script>
  // สมมติ Controller ส่งตัวแปร $lineChartMonths, $lineChartData, $pieLabels, $pieData

  let lineMonths = @json($lineChartMonths);
  let lineData = @json($lineChartData);
  let pieLabels = @json($pieLabels);
  let pieData = @json($pieData);
  let lineChartRole = @json($lineChartRole); // ใช้ระบุว่าเป็น "Teacher", "Student", "Admin", เป็นต้น

  // สร้าง Line Chart
  var ctx = document.getElementById("myAreaChart").getContext('2d');
  var myLineChart = new Chart(ctx, {
    type: 'line',
    data: {
      labels: lineMonths,
      datasets: [{
        // ตรงนี้จะใช้ label เป็น lineChartRole + " Logins"
        label: lineChartRole + " Logins",
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

  // สร้าง Pie Chart
  var ctxPie = document.getElementById("myPieChart").getContext('2d');
  var myPieChart = new Chart(ctxPie, {
    type: 'pie',
    data: {
      labels: pieLabels,
      datasets: [{
        data: pieData,
        backgroundColor: [
          '#4e73df',
          '#1cc88a',
          '#36b9cc',
          '#f6c23e'
        ]
      }]
    },
    options: {
      maintainAspectRatio: false,
      legend: {
        position: 'bottom'
      }
    }
  });
</script>
@endsection