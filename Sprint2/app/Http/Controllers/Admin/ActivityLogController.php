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

        $newFundsQuery = ActivityLog::where('action', 'create_fund');
        if ($request->has('role') && $request->role != '') {
            $newFundsQuery->where('role', $request->role);
        }
        // กรองเวลาสำหรับ newFunds
        if ($dateFilter == 'daily') {
            $newFundsQuery->whereDate('created_at', now()->toDateString());
        } elseif ($dateFilter == 'weekly') {
            $newFundsQuery->whereBetween('created_at', [
                now()->startOfWeek(),
                now()->endOfWeek()
            ]);
        } elseif ($dateFilter == 'monthly') {
            $newFundsQuery->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year);
        } elseif ($dateFilter == 'custom') {
            if ($startDate && $endDate) {
                $newFundsQuery->whereBetween('created_at', [$startDate, $endDate]);
            }
        }
        $newFundsCount = $newFundsQuery->count();

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
        // ถ้า $topUsage == 'language' ให้เราดึง log ของ change_language
        $topLanguages = [];
        if ($topUsage === 'language') {
            // query เฉพาะ action = 'change_language'
            $langQuery = ActivityLog::where('action', 'change_language');

            // กรอง role/dateFilter เหมือนกับส่วนอื่น
            if ($request->has('role') && $request->role != '') {
                $langQuery->where('role', $request->role);
            }
            if ($dateFilter == 'daily') {
                $langQuery->whereDate('created_at', now()->toDateString());
            } elseif ($dateFilter == 'weekly') {
                $langQuery->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
            } elseif ($dateFilter == 'monthly') {
                $langQuery->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year);
            } elseif ($dateFilter == 'custom') {
                if ($startDate && $endDate) {
                    $langQuery->whereBetween('created_at', [$startDate, $endDate]);
                }
            }

            // ดึงข้อมูล
            $logs = $langQuery->get();

            // parse หาภาษาจาก description เช่น "User foo switched language to en at 2023-01-01 12:00"
            $counts = [];
            foreach ($logs as $log) {
                // ใช้ regex หรือวิธีอื่นแยก "switched language to xx"
                if (preg_match('/language to (\w+)/', $log->description, $m)) {
                    $lang = $m[1]; // สมมติได้ "en" หรือ "th"
                    if (!isset($counts[$lang])) {
                        $counts[$lang] = 0;
                    }
                    $counts[$lang]++;
                }
            }
            // เรียงจากมากไปน้อย
            arsort($counts);
            // ตัด top 3 (หรือ top 5 แล้วแต่)
            $counts = array_slice($counts, 0, 3, true);

            // เก็บลงตัวแปร $topLanguages (รูปแบบ [ 'en' => 10, 'th' => 8, ... ])
            $topLanguages = $counts;
        }

        // 4) นับ New Research Projects
        $newResearchProjQuery = ActivityLog::where('action', 'create_research_project');
        if ($request->has('role') && $request->role != '') {
            $newResearchProjQuery->where('role', $request->role);
        }
        // กรองเวลาสำหรับ newResearchProjects
        if ($dateFilter == 'daily') {
            $newResearchProjQuery->whereDate('created_at', now()->toDateString());
        } elseif ($dateFilter == 'weekly') {
            $newResearchProjQuery->whereBetween('created_at', [
                now()->startOfWeek(),
                now()->endOfWeek()
            ]);
        } elseif ($dateFilter == 'monthly') {
            $newResearchProjQuery->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year);
        } elseif ($dateFilter == 'custom') {
            if ($startDate && $endDate) {
                $newResearchProjQuery->whereBetween('created_at', [$startDate, $endDate]);
            }
        }
        $newResearchProjectsCount = $newResearchProjQuery->count();


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

        // จากนั้น นับเฉพาะแต่ละ action
        $createUserCount = (clone $totalActivitiesQuery)->where('action', 'create_user')->count();
        $createFundCount = (clone $totalActivitiesQuery)->where('action', 'create_fund')->count();
        $createResearchCount = (clone $totalActivitiesQuery)->where('action', 'create_research_project')->count();
        $createDeptCount = (clone $totalActivitiesQuery)->where('action', 'create_department')->count();

        // คำนวณเปอร์เซ็นต์ (ถ้า totalActivities = 0 ต้องกันไม่ให้หารศูนย์)
        $createUserPercent = $totalActivities > 0 ? ($createUserCount / $totalActivities) * 100 : 0;
        $createFundPercent = $totalActivities > 0 ? ($createFundCount / $totalActivities) * 100 : 0;
        $createResearchPercent = $totalActivities > 0 ? ($createResearchCount / $totalActivities) * 100 : 0;
        $createDeptPercent = $totalActivities > 0 ? ($createDeptCount / $totalActivities) * 100 : 0;

        // ปัดเศษสักเล็กน้อย
        $createUserPercent = number_format($createUserPercent, 0);
        $createFundPercent = number_format($createFundPercent, 0);
        $createResearchPercent = number_format($createResearchPercent, 0);
        $createDeptPercent = number_format($createDeptPercent, 0);

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
        $pieCategories = [
            // ตัวอย่างหมวด "Auth / Login"
            'auth_actions' => [
                'login',
                'change_language',
            ],
            // หมวด "User Management"
            'user_management' => [
                'create_user',
                'view_user_detail',
                'edit_user',
                'delete_user',
                'import_user',
                'call_scopus_api',
                // ถ้ามองว่า call_scopus_api เกี่ยวกับการจัดการ user ก็ใส่ได้
            ],
            // หมวด "Book / Patent"
            'book_patent' => [
                'store_book',
                'view_book_detail',
                'store_patent',
                'view_patent_detail',
            ],
            // หมวด "Department / Program"
            'dept_program' => [
                'create_department',
                'view_department_detail',
                'edit_department',
                'delete_department',
                'create_program',
                'edit_program',
                'delete_program',
            ],
            // หมวด "Fund"
            'fund' => [
                'create_fund',
                'view_fund',
                'update_fund',
                'delete_fund',
            ],
            // หมวด "Permission / Role"
            'permission_role' => [
                'create_permission',
                'view_permission_detail',
                'edit_permission',
                'delete_permission',
                'create_role',
                'view_role_detail',
                'edit_role',
                'delete_role',
            ],
            // หมวด "Research"
            'research' => [
                'create_research_project',
                'view_research_project',
                'update_research_project',
                'delete_research_project',
                'create_research_group',
                'view_research_group',
                'update_research_group',
                'delete_research_group',
                'view_researchers',
                'search_researchers',
            ],
            // หมวด "File"
            'file_stuff' => [
                'file_upload',
                'file_download',
                'export_paper',
                'view_export_page',
            ],

            // หมวด Default หรือหมวดรวมทั้งหมด (กรณีอยากให้มีหมวด "ทั้งหมด")
            'all_actions' => [
                'login',
                'change_language',
                'create_user',
                'view_user_detail',
                'edit_user',
                'delete_user',
                'import_user',
                'call_scopus_api',
                'store_book',
                'view_book_detail',
                'store_patent',
                'view_patent_detail',
                'create_department',
                'view_department_detail',
                'edit_department',
                'delete_department',
                'create_program',
                'edit_program',
                'delete_program',
                'create_fund',
                'view_fund',
                'update_fund',
                'delete_fund',
                'create_permission',
                'view_permission_detail',
                'edit_permission',
                'delete_permission',
                'create_role',
                'view_role_detail',
                'edit_role',
                'delete_role',
                'create_research_project',
                'view_research_project',
                'update_research_project',
                'delete_research_project',
                'create_research_group',
                'view_research_group',
                'update_research_group',
                'delete_research_group',
                'view_researchers',
                'search_researchers',
                'export_paper',
                'view_export_page',
                'file_upload',
                'file_download',
            ],
        ];

        // 2) รับค่า pie_filter
        $pieFilter = $request->input('pie_filter', 'all_actions');
        // ถ้าไม่ส่งมา จะ default เป็น 'all_actions' (หรือค่าอื่นที่คุณต้องการ)

        // 2.1) ถ้าใน $pieCategories มี key ตรงกับ $pieFilter → ให้ $actions = $pieCategories[$pieFilter]
        if (isset($pieCategories[$pieFilter])) {
            $actions = $pieCategories[$pieFilter];
        } else {
            // ถ้าไม่พบ key => default เป็น all_actions หรือ create_stuff ก็ได้
            $actions = $pieCategories['all_actions'];
        }

        // 3) สร้าง Query เบื้องต้น
        $pieQuery = ActivityLog::whereIn('action', $actions);

        // filter role, dateFilter เหมือนกัน
        if ($request->has('role') && $request->role != '') {
            $pieQuery->where('role', $request->role);
        }

        // กรองช่วงเวลา
        if ($dateFilter == 'daily') {
            $pieQuery->whereDate('created_at', now()->toDateString());
        } elseif ($dateFilter == 'weekly') {
            $pieQuery->whereBetween('created_at', [
                now()->startOfWeek(),
                now()->endOfWeek()
            ]);
        } elseif ($dateFilter == 'monthly') {
            $pieQuery->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year);
        } elseif ($dateFilter == 'custom') {
            if ($startDate && $endDate) {
                $pieQuery->whereBetween('created_at', [$startDate, $endDate]);
            }
        }

        // groupBy action
        $pieQuery = $pieQuery->selectRaw('action, COUNT(*) as total')
            ->groupBy('action')
            ->get();

        // แปลงให้อยู่ในรูป Array เพื่อส่งไปที่ View
        $pieLabels = [];
        $pieData   = [];

        foreach ($pieQuery as $row) {
            $pieLabels[] = $row->action; // เช่น create_user
            $pieData[]   = $row->total;  // จำนวน
        }



        return view(
            'dashboards.users.activity-report.index',
            compact(
                'activities',
                'loginCount',
                'newUsersCount',
                'newFundsCount',
                'newResearchProjectsCount',
                'lineChartMonths',
                'lineChartData',
                'lineChartRole',
                'pieLabels',
                'pieData',
                'createUserPercent',
                'createFundPercent',
                'createResearchPercent',
                'createDeptPercent',
                'topPrograms',
                'topLanguages'
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
