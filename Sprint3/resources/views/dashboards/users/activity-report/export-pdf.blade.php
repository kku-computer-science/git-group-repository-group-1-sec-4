<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Activity Report PDF</title>
    <style>
      /* กำหนด margin ของ PDF */
      @page {
        margin: 25mm 15mm;  /* top-bottom = 25mm, left-right = 15mm */
      }
      body {
        margin: 0; 
        padding: 0;
        font-family: "DejaVu Sans", sans-serif;
        font-size: 14px;
      }
      .header {
        text-align: center;
        margin-bottom: 30px;
      }
      .summary, .key-insights {
        margin-bottom: 20px;
        border: 1px solid #000;
        padding: 10px;
      }
      .summary h2, .key-insights h2 {
        margin-top: 0;
        background: #eee;
        padding: 5px;
      }
      .summary-list {
        list-style-type: none;
        margin: 0; padding: 0;
      }
      .summary-list li {
        margin-bottom: 5px;
      }
      .appendix {
        margin-top: 30px;
      }
      .appendix h2 {
        background: #eee;
        padding: 5px;
        margin-top: 0;
      }
      /* ปรับตารางให้มีความกว้าง 95%, อยู่กึ่งกลาง และลด padding */
      .table-wrapper {
        width: 95%;
        margin: 0 auto;
      }
      .log-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
      }
      .log-table th, .log-table td {
        border: 1px solid #ccc;
        padding: 6px; /* ลด padding ลง */
        font-size: 12px; /* ลดขนาด font ถ้าต้องการ */
      }
      .log-table th {
        background: #f2f2f2;
        text-align: left;
      }
    </style>
</head>
<body>

  <!-- ส่วนหัวรายงาน -->
  <div class="header">
    <h1>Activity Report</h1>
    <p>
      Role: {{ $role ?: 'All' }} | 
      Date Range:
      @if($date_filter=='daily') Today 
      @elseif($date_filter=='weekly') This Week
      @elseif($date_filter=='monthly') This Month
      @elseif($date_filter=='custom')
         {{ $startDate }} to {{ $endDate }}
      @else
         All Time
      @endif
    </p>
  </div>

  <!-- สรุป (Summary) -->
  <div class="summary">
    <h2>Summary</h2>
    <ul class="summary-list">
      <li><strong>Total Logins:</strong> {{ $loginCount ?? 0 }}</li>
      <li><strong>New Users:</strong> {{ $newUsersCount ?? 0 }}</li>
      <li><strong>New Funds:</strong> {{ $newFundsCount ?? 0 }}</li>
      <li><strong>New Research Projects:</strong> {{ $newResearchProjectsCount ?? 0 }}</li>
      <li><strong>Create User %:</strong> {{ number_format($createUserPercent, 2) }}%</li>
    </ul>
  </div>

  <!-- Key Insights / Highlights -->
  <div class="key-insights">
    <h2>Key Insights / Highlights</h2>
    @if(!empty($keyInsights))
      <ul>
        @foreach($keyInsights as $insight)
          <li>{{ $insight }}</li>
        @endforeach
      </ul>
    @else
      <p>No specific insights found.</p>
    @endif
  </div>

  <!-- ภาคผนวก: Detailed Activities -->
  <div class="appendix">
    <h2>Appendix: Detailed Activities</h2>
    <p style="font-style: italic;">(This section shows all logs; you can limit the number if needed)</p>

    <div class="table-wrapper">
      <table class="log-table">
        <thead>
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
          @foreach($activities as $act)
          <tr>
            <td>{{ $act->id }}</td>
            <td>{{ $act->user ? $act->user->email : 'N/A' }}</td>
            <td>{{ $act->role ?? 'N/A' }}</td>
            <td>{{ $act->action }}</td>
            <td>{{ $act->description }}</td>
            <td>{{ $act->created_at }}</td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>

</body>
</html>
