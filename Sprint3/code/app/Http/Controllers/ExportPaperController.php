<?php

namespace App\Http\Controllers;

use App\Exports\ExportPaper;
use App\Exports\ExportUser;
use App\Exports\UsersExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\ActivityLog;

class ExportPaperController extends Controller
{
    public function exportUsers(Request $request){
        $export = new ExportUser([
            [1, 2, 3],
            [4, 5, 6]
        ]);
        ActivityLog::create([
            'user_id' => auth()->check() ? auth()->user()->id : null,
            'role'    => auth()->check() ? auth()->user()->roles->pluck('name')->first() : 'guest',
            'action'  => 'export_paper',
            'description' => 'User ' . (auth()->check() ? auth()->user()->email : 'guest') .
                             ' exported paper data at ' . now()
        ]);
        return Excel::download(new $export, 'new.csv');
    }
}
