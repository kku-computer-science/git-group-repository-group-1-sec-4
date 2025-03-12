<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ActivityLog;
use Carbon\Carbon;

class ActivityLogSeeder extends Seeder
{
    public function run()
    {
        // รายการ action ทั้งหมด
        $actions = [
            'login',
            'create_user',
            'view_user_detail',
            'edit_user',
            'delete_user',
            'store_book',
            'view_book_detail',
            'create_department',
            'view_department_detail',
            'edit_department',
            'delete_department',
            'view_export_page',
            'export_paper',
            'file_upload',
            'file_download',
            'create_fund',
            'view_fund',
            'update_fund',
            'delete_fund',
            'import_user',
            'change_language',
            'store_patent',
            'view_patent_detail',
            'create_permission',
            'view_permission_detail',
            'edit_permission',
            'delete_permission',
            'create_program',
            'edit_program',
            'delete_program',
            'view_researchers',
            'search_researchers',
            'create_research_group',
            'view_research_group',
            'update_research_group',
            'delete_research_group',
            'create_research_project',
            'view_research_project',
            'update_research_project',
            'delete_research_project',
            'create_role',
            'view_role_detail',
            'edit_role',
            'delete_role',
            'call_scopus_api'
        ];

        // สมมติสร้างข้อมูล 50 รายการ
        for ($i = 1; $i <= 50; $i++) {
            ActivityLog::create([
                // เลือก user_id แบบสุ่ม (ในที่นี้ระบุ 1-10; ปรับตามระบบของคุณ)
                'user_id'    => rand(1, 10),
                // เลือก role แบบสุ่มจากรายชื่อที่กำหนดไว้
                'role'       => collect(['admin', 'teacher', 'student', 'staff', 'headproject', 'guest'])->random(),
                // เลือก action แบบสุ่มจากอาร์เรย์ $actions
                'action'     => $actions[array_rand($actions)],
                // กำหนด description แบบง่ายๆ พร้อมหมายเลขรายการ
                'description'=> 'Mock data for testing #' . $i,
                // สุ่มวันที่ย้อนหลัง 1 ถึง 60 วัน
                'created_at' => Carbon::now()->subDays(rand(1, 60)),
                'updated_at' => Carbon::now(),
            ]);
        }
    }
}
