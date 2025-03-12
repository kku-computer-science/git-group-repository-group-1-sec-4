<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ActivityLog;
use Barryvdh\DomPDF\Facade\Pdf;

class ActivityLogController extends Controller
{

    public function index(Request $request)
    {
        // สร้าง query พื้นฐาน
        $query = ActivityLog::with('user')->orderBy('created_at', 'desc');

        // กรองตาม role ถ้ามีการส่งมา
        if ($request->has('role') && $request->role != '') {
            $query->where('role', $request->role);
        }
        $dateFilter = $request->input('date_filter');  // daily, weekly, monthly, custom
        $startDate  = $request->input('start_date');
        $endDate    = $request->input('end_date');

        if ($dateFilter == 'daily') {
            // วันนี้
            $query->whereDate('created_at', now()->toDateString());
        } elseif ($dateFilter == 'weekly') {
            // สัปดาห์นี้ (จันทร์-อาทิตย์) หรือแล้วแต่ policy
            $query->whereBetween('created_at', [
                now()->startOfWeek(),
                now()->endOfWeek()
            ]);
        } elseif ($dateFilter == 'monthly') {
            // เดือนนี้
            $query->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year);
        } elseif ($dateFilter == 'custom') {
            // ช่วงวันที่กำหนดเอง (start_date, end_date)
            // อย่าลืม validate และแปลงให้เป็น Carbon
            if ($startDate && $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate]);
            }
        }

        $activities = $query->paginate(10);

        $loginCountQuery = ActivityLog::where('action', 'login');
        if ($request->has('role') && $request->role != '') {
            $loginCountQuery->where('role', $request->role);
        }
        if ($dateFilter == 'daily') {
            $loginCountQuery->whereDate('created_at', now()->toDateString());
        } elseif ($dateFilter == 'weekly') {
            $loginCountQuery->whereBetween('created_at', [
                now()->startOfWeek(),
                now()->endOfWeek()
            ]);
        } elseif ($dateFilter == 'monthly') {
            $loginCountQuery->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year);
        } elseif ($dateFilter == 'custom') {
            if ($startDate && $endDate) {
                $loginCountQuery->whereBetween('created_at', [$startDate, $endDate]);
            }
        }

        $loginCount = $loginCountQuery->count();



        $newUsersQuery = ActivityLog::where('action', 'create_user');

        // กรอง role
        if ($request->has('role') && $request->role != '') {
            $newUsersQuery->where('role', $request->role);
        }

        // กรองช่วงเวลาเหมือนกัน
        if ($dateFilter == 'daily') {
            $newUsersQuery->whereDate('created_at', now()->toDateString());
        } elseif ($dateFilter == 'weekly') {
            $newUsersQuery->whereBetween('created_at', [
                now()->startOfWeek(),
                now()->endOfWeek()
            ]);
        } elseif ($dateFilter == 'monthly') {
            $newUsersQuery->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year);
        } elseif ($dateFilter == 'custom') {
            if ($startDate && $endDate) {
                $newUsersQuery->whereBetween('created_at', [$startDate, $endDate]);
            }
        }

        $newUsersCount = $newUsersQuery->count();

        $loginFailuresQuery = ActivityLog::where('action', 'login_failed');

        // กรอง role ถ้ามี
        if ($request->has('role') && $request->role != '') {
            $loginFailuresQuery->where('role', $request->role);
        }

        // กรองช่วงเวลาตาม date_filter (daily, weekly, monthly, custom)
        if ($dateFilter == 'daily') {
            $loginFailuresQuery->whereDate('created_at', now()->toDateString());
        } elseif ($dateFilter == 'weekly') {
            $loginFailuresQuery->whereBetween('created_at', [
                now()->startOfWeek(),
                now()->endOfWeek()
            ]);
        } elseif ($dateFilter == 'monthly') {
            $loginFailuresQuery->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year);
        } elseif ($dateFilter == 'custom') {
            if ($startDate && $endDate) {
                $loginFailuresQuery->whereBetween('created_at', [$startDate, $endDate]);
            }
        }

        // สุดท้ายนับจำนวน
        $loginFailuresCount = $loginFailuresQuery->count();


        // 2) นับ Delete Users
        $deleteUsersQuery = ActivityLog::where('action', 'delete_user');

        if ($request->has('role') && $request->role != '') {
            $deleteUsersQuery->where('role', $request->role);
        }

        // กรองช่วงเวลาตาม date_filter
        if ($dateFilter == 'daily') {
            $deleteUsersQuery->whereDate('created_at', now()->toDateString());
        } elseif ($dateFilter == 'weekly') {
            $deleteUsersQuery->whereBetween('created_at', [
                now()->startOfWeek(),
                now()->endOfWeek()
            ]);
        } elseif ($dateFilter == 'monthly') {
            $deleteUsersQuery->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year);
        } elseif ($dateFilter == 'custom') {
            if ($startDate && $endDate) {
                $deleteUsersQuery->whereBetween('created_at', [$startDate, $endDate]);
            }
        }
        $deleteUsersCount = $deleteUsersQuery->count();



        $guestViewQuery = ActivityLog::where('action', 'view_researchers')
            ->where('role', 'guest');

        // ถ้ามี dateFilter หรือ role เพิ่มเติม เช่น monthly, daily ฯลฯ ก็ประยุกต์เหมือนด้านบน
        if ($dateFilter == 'daily') {
            $guestViewQuery->whereDate('created_at', now()->toDateString());
        } elseif ($dateFilter == 'weekly') {
            $guestViewQuery->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
        } elseif ($dateFilter == 'monthly') {
            $guestViewQuery->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year);
        } elseif ($dateFilter == 'custom') {
            if ($startDate && $endDate) {
                $guestViewQuery->whereBetween('created_at', [$startDate, $endDate]);
            }
        }

        // 2) ดึงข้อมูลมาเป็น Collection เพื่อนำไป parse program_id ออกจาก description
        $guestViews = $guestViewQuery->get();

        // 3) นับจำนวนครั้งที่ program_id ใด ๆ ถูก view (ใช้ array เก็บ)
        $counts = [];  // key=program_id, value=จำนวนครั้ง

        foreach ($guestViews as $log) {
            // ตัวอย่าง description: "User Guest viewed researcher list in program: Computer Sci (ID=5)"
            // ใช้ regex หรือวิธีอื่น parse "ID=..."
            if (preg_match('/\(ID=(\d+)\)/', $log->description, $m)) {
                $pid = $m[1];  // เช่น "5"
                if (!isset($counts[$pid])) {
                    $counts[$pid] = 0;
                }
                $counts[$pid]++;
            }
        }

        // 4) เรียงลำดับนับ desc
        arsort($counts); // ค่ามาก → น้อย
        $topUsage = $request->input('top_usage', 'program'); // ค่าดีฟอลต์เป็น 'program'
        // 5) ตัด top 5 (หรือ top 3 แล้วแต่ต้องการ)
        $topProgramIds = array_slice(array_keys($counts), 0, 5); // เอาเฉพาะ key (program_id) 5 อันแรก

        // 6) ดึงข้อมูล Program จริง ๆ จากตาราง
        //    โดยต้องรักษาลำดับให้ตรงกับที่นับไว้
        //    วิธีง่ายสุด: ดึง Program ทั้งหมด แล้ว sort เอง
        //    หรือจะ map result เป็น array
        $topPrograms = [];
        foreach ($topProgramIds as $pid) {
            $p = \App\Models\Program::find($pid);
            if ($p) {
                $topPrograms[] = [
                    'program' => $p,           // instance ของ Program
                    'count'   => $counts[$pid] // จำนวนครั้ง
                ];
            }
        }
        // เพิ่มตัวแปรเก็บ topEmails (default = [])
        $topEmails = [];

        // ตรวจสอบตัวแปร top_usage
        $topUsage = $request->input('top_usage', 'program'); // default = 'program'

        // ถ้าเลือก 'program' → แสดง Top Programs เหมือนเดิม
        // ถ้าเลือก 'email' → ทำ logic สำหรับ top emails
        if ($topUsage === 'email') {
            // 1) เริ่มจาก query ActivityLog ทั้งหมด
            $emailQuery = ActivityLog::with('user'); // อย่าลืมกับ user() เพื่อจะได้ไปดึง email ด้วย

            // 2) กรอง role, date_filter เหมือนเดิม
            if ($request->has('role') && $request->role != '') {
                $emailQuery->where('role', $request->role);
            }
            if ($dateFilter == 'daily') {
                $emailQuery->whereDate('created_at', now()->toDateString());
            } elseif ($dateFilter == 'weekly') {
                $emailQuery->whereBetween('created_at', [
                    now()->startOfWeek(),
                    now()->endOfWeek()
                ]);
            } elseif ($dateFilter == 'monthly') {
                $emailQuery->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year);
            } elseif ($dateFilter == 'custom') {
                if ($startDate && $endDate) {
                    $emailQuery->whereBetween('created_at', [$startDate, $endDate]);
                }
            }

            // 3) ดึงผลลัพธ์เป็น Collection
            $logs = $emailQuery->get();

            // 4) นับจำนวน (groupBy email) - สมมติ user อาจเป็น null → กันไว้
            $counts = []; // key = email, value = count
            foreach ($logs as $log) {
                // user()->email อาจจะ null → กันกรณีไม่เจอ user
                $userEmail = optional($log->user)->email ?? 'N/A';
                if (!isset($counts[$userEmail])) {
                    $counts[$userEmail] = 0;
                }
                $counts[$userEmail]++;
            }

            // เรียงมาก→น้อย
            arsort($counts);

            // สมมติเอา Top 5
            $counts = array_slice($counts, 0, 5, true);

            // เก็บลงตัวแปร $topEmails
            // โครงสร้างเป็น [ 'email1@example.com' => 50, 'user2@example.com' => 45, ... ]
            $topEmails = $counts;
        }

        $totalActivitiesQuery = ActivityLog::query();

        // ถ้ามีการกรอง role
        if ($request->has('role') && $request->role != '') {
            $totalActivitiesQuery->where('role', $request->role);
        }

        // ถ้ามีการกรองช่วงเวลา (daily, weekly, monthly, custom) เช่นเดียวกับในโค้ดหลัก
        if ($dateFilter == 'daily') {
            $totalActivitiesQuery->whereDate('created_at', now()->toDateString());
        } elseif ($dateFilter == 'weekly') {
            $totalActivitiesQuery->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
        } elseif ($dateFilter == 'monthly') {
            $totalActivitiesQuery->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year);
        } elseif ($dateFilter == 'custom') {
            if ($startDate && $endDate) {
                $totalActivitiesQuery->whereBetween('created_at', [$startDate, $endDate]);
            }
        }

        // นับ total activities ทั้งหมด
        $totalActivities = $totalActivitiesQuery->count();



        $teacherLoginQuery = ActivityLog::where('role', 'teacher')
            ->where('action', 'login');

        if ($dateFilter == 'daily') {
            $teacherLoginQuery->whereDate('created_at', now()->toDateString());
        } elseif ($dateFilter == 'weekly') {
            $teacherLoginQuery->whereBetween('created_at', [
                now()->startOfWeek(),
                now()->endOfWeek()
            ]);
        } elseif ($dateFilter == 'monthly') {
            $teacherLoginQuery->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year);
        } elseif ($dateFilter == 'custom') {
            if ($startDate && $endDate) {
                $teacherLoginQuery->whereBetween('created_at', [$startDate, $endDate]);
            }
        }
        $teacherLoginPerMonth = $teacherLoginQuery
            ->selectRaw('MONTH(created_at) as month, COUNT(*) as total')
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $lineFilter = $request->input('line_filter', 'teacher');
        // ถ้าไม่มีค่าจะใช้ 'teacher' เป็น default

        // สมมติว่าเราจะ query ตาราง activity_logs
        // เพื่อดูจำนวนการ login ของ role ที่เลือก (lineFilter) แยกรายเดือน
        $loginQuery = ActivityLog::where('action', 'login')
            ->where('role', $lineFilter);

        // เพิ่มเงื่อนไขกรองตาม dateFilter (daily, weekly, monthly, custom) เหมือนเดิม
        if ($dateFilter == 'daily') {
            $loginQuery->whereDate('created_at', now()->toDateString());
        } elseif ($dateFilter == 'weekly') {
            $loginQuery->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
        } elseif ($dateFilter == 'monthly') {
            $loginQuery->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year);
        } elseif ($dateFilter == 'custom') {
            if ($startDate && $endDate) {
                $loginQuery->whereBetween('created_at', [$startDate, $endDate]);
            }
        }

        // จากนั้น groupBy เดือน แล้วนับจำนวน
        // ตัวอย่าง: select MONTH(created_at) as month, COUNT(*) as total
        $loginPerMonth = $loginQuery
            ->selectRaw('MONTH(created_at) as month, COUNT(*) as total')
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $lineChartMonths = [];
        $lineChartData   = [];
        foreach ($loginPerMonth as $item) {
            $lineChartMonths[] = $item->month;
            $lineChartData[]   = $item->total;
        }
        $lineChartRole = ucfirst($lineFilter);

        return view(
            'dashboards.users.activity-report.index',
            compact(
                'activities',
                'loginCount',
                'newUsersCount',
                'loginFailuresCount',
                'deleteUsersCount',
                'lineChartMonths',
                'lineChartData',
                'lineChartRole',
                'topPrograms',
                'topEmails'
            )
        );
    }
    public function exportPDF(Request $request)
    {
        // 1) ดึงข้อมูลแบบเดียวกับใน index() แต่ไม่ paginate
        $query = ActivityLog::with('user')->orderBy('created_at', 'desc');

        if ($request->has('role') && $request->role != '') {
            $query->where('role', $request->role);
        }

        $dateFilter = $request->input('date_filter');
        $startDate  = $request->input('start_date');
        $endDate    = $request->input('end_date');

        // ตัวอย่างการกรองช่วงเวลา
        if ($dateFilter == 'daily') {
            $query->whereDate('created_at', now()->toDateString());
        } elseif ($dateFilter == 'weekly') {
            $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
        } elseif ($dateFilter == 'monthly') {
            $query->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year);
        } elseif ($dateFilter == 'custom') {
            if ($startDate && $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate]);
            }
        }

        // ดึงทั้งหมด (ไม่ paginate)
        $activities = $query->get();

        // 2) เก็บสถิติต่าง ๆ (เช่นเดียวกับใน index)
        $loginCount = ActivityLog::where('action', 'login')->count();
        $newUsersCount = ActivityLog::where('action', 'create_user')->count();
        $newFundsCount = ActivityLog::where('action', 'create_fund')->count();
        $newResearchProjectsCount = ActivityLog::where('action', 'create_research_project')->count();

        $totalActivities = ActivityLog::count();
        $createUserCount = ActivityLog::where('action', 'create_user')->count();
        $createUserPercent = $totalActivities > 0
            ? ($createUserCount / $totalActivities) * 100
            : 0;

        // **ตัวอย่าง** สร้าง keyInsights (array) ที่จะส่งไปให้ View
        // เช่น สรุป Top 3 Actions, หรือ top 3 Roles ที่ใช้งานเยอะที่สุด
        $keyInsights = [];

        // ตัวอย่าง 1: สรุป top 3 actions
        $topActions = ActivityLog::selectRaw('action, COUNT(*) as total')
            ->groupBy('action')
            ->orderByDesc('total')
            ->limit(3)
            ->get();

        // แปลงข้อมูล topActions เป็นข้อความสั้น ๆ
        // เช่น "1) login (10 times), 2) create_user (7 times), 3) file_upload (5 times)"
        if ($topActions->count() > 0) {
            $desc = $topActions->map(function ($item, $idx) {
                return ($idx + 1) . ') ' . $item->action . ' (' . $item->total . ' times)';
            })->join(', ');
            $keyInsights[] = "Top 3 actions: " . $desc;
        }

        // ตัวอย่าง 2: สรุป Top role ที่เจอบ่อยสุด (ยกตัวอย่าง)
        $topRoles = ActivityLog::selectRaw('role, COUNT(*) as total')
            ->whereNotNull('role')
            ->groupBy('role')
            ->orderByDesc('total')
            ->limit(2)
            ->get();
        if ($topRoles->count() > 0) {
            $desc2 = $topRoles->map(function ($item, $idx) {
                return ($idx + 1) . ') ' . $item->role . ' (' . $item->total . ' times)';
            })->join(', ');
            $keyInsights[] = "Roles with highest activities: " . $desc2;
        }

        // 3) โหลด view สำหรับ PDF
        $pdf = Pdf::loadView('dashboards.users.activity-report.export-pdf', [
            'activities' => $activities,

            // สถิติเบื้องต้น
            'loginCount' => $loginCount,
            'newUsersCount' => $newUsersCount,
            'newFundsCount' => $newFundsCount,
            'newResearchProjectsCount' => $newResearchProjectsCount,
            'createUserPercent' => $createUserPercent,

            // ข้อมูลเสริม
            'keyInsights' => $keyInsights,

            // เอา role, date_filter ไปแสดงในไฟล์ pdf ด้วย
            'role' => $request->role,
            'date_filter' => $request->date_filter,
            'startDate' => $request->start_date,
            'endDate' => $request->end_date,
        ]);

        // 4) return download
        return $pdf->download('ActivityReport.pdf');
    }
}
