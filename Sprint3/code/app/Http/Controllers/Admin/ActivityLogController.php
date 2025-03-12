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



        $actionListQuery = clone $query;

        // 3) ส่วนของการ filter action
        $actionFilter = $request->input('action_filter');
        if ($actionFilter && $actionFilter != '') {
            $query->where('action', $actionFilter);
        }

        // 4) สุดท้ายค่อย paginate
        $activities = $query->paginate(10);

        // ***** แก้ไขตรงนี้ *****
        // ลบคำสั่ง orderBy('created_at','desc') ออกจาก $actionListQuery
        // วิธีง่าย ๆ คือ reset order ด้วย ->reorder() หรือเคลียร์ orders
        $actionListQuery->getQuery()->orders = null;

        // 5) ดึง distinct action จาก $actionListQuery ที่ไม่มี orderBy
        $distinctActions = $actionListQuery
            ->select('action')
            ->distinct()
            ->pluck('action');

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

        $newUsersLogs = $newUsersQuery->get(); // ดึงทั้งหมดก่อน

        $newUsersCount = 0; // เดี๋ยวเราจะคำนวณเอง

        // ถ้ามี request('role') เช่น teacher, student, ...
        if ($request->has('role') && $request->role != '') {
            foreach ($newUsersLogs as $log) {
                // ตัวอย่าง description: "User admin@gmail.com created a new user ID = 12"
                // ใช้ regex ดึงเลข user ID = (\d+)
                if (preg_match('/user\s+ID\s*=\s*(\d+)/i', $log->description, $m)) {
                    $createdUserId = $m[1];
                    // ไปหา user จริง ๆ
                    $createdUser = \App\Models\User::find($createdUserId);
                    if ($createdUser && $createdUser->hasRole($request->role)) {
                        $newUsersCount++;
                    }
                }
            }
        } else {
            // กรณีไม่ได้กรอง role => แสดงจำนวน log create_user ทั้งหมด
            $newUsersCount = $newUsersLogs->count();
        }

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
        $deleteUsersLogs = $deleteUsersQuery->get();

        $deleteUsersCount = 0;

        if ($request->has('role') && $request->role != '') {
            foreach ($deleteUsersLogs as $log) {
                // ตัวอย่าง description ที่เราแก้ไขใน UserController@destroy:
                // "User admin@gmail.com deleted user ID = 33 (target_role=teacher)"
                if (preg_match('/target_role\s*=\s*([\w]+)/i', $log->description, $m)) {
                    $targetRole = strtolower($m[1]);
                    if ($targetRole == strtolower($request->role)) {
                        $deleteUsersCount++;
                    }
                }
            }
        } else {
            // ไม่ได้กรอง role => นับทั้งหมด
            $deleteUsersCount = $deleteUsersLogs->count();
        }

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

        // สร้าง query สำหรับนับ logins (action = 'login') โดยใช้ filter role + date_filter เหมือนกับตาราง
        $chartQuery = ActivityLog::where('action', 'login');

        // ถ้ามีการเลือก role
        if ($request->has('role') && $request->role != '') {
            $chartQuery->where('role', $request->role);
        }

        // จากนั้นประยุกต์เงื่อนไข dateFilter เหมือนที่ใช้ใน $query
        if ($dateFilter == 'daily') {
            $chartQuery->whereDate('created_at', now()->toDateString());
        } elseif ($dateFilter == 'weekly') {
            $chartQuery->whereBetween('created_at', [
                now()->startOfWeek(),
                now()->endOfWeek()
            ]);
        } elseif ($dateFilter == 'monthly') {
            $chartQuery->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year);
        } elseif ($dateFilter == 'custom') {
            if ($startDate && $endDate) {
                $chartQuery->whereBetween('created_at', [$startDate, $endDate]);
            }
        }

        // สมมติเราจะ groupBy วันที่ (DATE(created_at)) เพื่อดูยอดต่อวัน
        // ถ้าอยากเปลี่ยนเป็น groupBy เดือน ก็เปลี่ยน DATE(created_at) เป็น MONTH(created_at) ได้
        $chartData = $chartQuery->selectRaw('DATE(created_at) as date, COUNT(*) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // แปลงข้อมูลเป็น array สำหรับส่งไปยัง blade
        $lineChartLabels = $chartData->pluck('date');  // รายการวันที่
        $lineChartValues = $chartData->pluck('total'); // จำนวน login ต่อวัน

        // กำหนดชื่อกราฟตาม date_filter
        $chartTitle = 'Logins';
        switch ($dateFilter) {
            case 'daily':
                $chartTitle .= ' Today';
                break;
            case 'weekly':
                $chartTitle .= ' This Week';
                break;
            case 'monthly':
                $chartTitle .= ' This Month';
                break;
            case 'custom':
                // สมมติว่าแสดงช่วงวัน เช่น 2025-03-01 to 2025-03-07
                $chartTitle .= " ({$startDate} to {$endDate})";
                break;
            default:
                $chartTitle .= ' (All Time)';
                break;
        }

        // ถ้ามีเลือก role ก็เติมต่อท้าย
        if ($request->role) {
            $chartTitle .= ' (' . ucfirst($request->role) . ')';
        }

        // 1) สร้าง query สำหรับนับการ call paper
        $callPaperQuery = ActivityLog::where('action', 'call_scopus_api');
        // หรือถ้าเปลี่ยนเป็น 'call_paper' ก็ได้ตาม action ที่คุณกำหนด

        // 2) กรองเฉพาะ Date Filter (ไม่สน role)
        // (copy เงื่อนไขเดียวกับส่วน loginCountQuery หรือ newUsersQuery เกี่ยวกับวันที่)
        if ($dateFilter == 'daily') {
            $callPaperQuery->whereDate('created_at', now()->toDateString());
        } elseif ($dateFilter == 'weekly') {
            $callPaperQuery->whereBetween('created_at', [
                now()->startOfWeek(),
                now()->endOfWeek()
            ]);
        } elseif ($dateFilter == 'monthly') {
            $callPaperQuery->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year);
        } elseif ($dateFilter == 'custom') {
            if ($startDate && $endDate) {
                $callPaperQuery->whereBetween('created_at', [$startDate, $endDate]);
            }
        }

        // 3) สุดท้ายนับจำนวน
        $callPaperCount = $callPaperQuery->count();

        return view(
            'dashboards.users.activity-report.index',
            compact(
                'activities',
                'distinctActions',
                'actionFilter',
                'loginCount',
                'newUsersCount',
                'loginFailuresCount',
                'deleteUsersCount',
                'chartTitle',
                'lineChartLabels',
                'lineChartValues',
                'callPaperCount',
                'topPrograms',
                'topEmails'
            )
        );
    }
}
