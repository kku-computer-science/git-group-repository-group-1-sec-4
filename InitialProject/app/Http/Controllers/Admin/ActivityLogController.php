<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ActivityLog;

class ActivityLogController extends Controller
{
    public function index()
    {
        $activities = ActivityLog::with('user')->orderBy('created_at','desc')->paginate(20);
        return view('dashboards.users.activity-report.index', compact('activities'));
    }
}
