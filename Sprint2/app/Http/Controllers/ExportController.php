<?php

namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use App\Models\ActivityLog;

class ExportController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:export')->only('index');
        // Redirect::to('dashboard')->send();
         
    }
    public function index()
    {
        ActivityLog::create([
            'user_id' => auth()->check() ? auth()->user()->id : null,
            'role'    => auth()->check() ? auth()->user()->roles->pluck('name')->first() : 'guest',
            'action'  => 'view_export_page',
            'description' => 'User ' . (auth()->check() ? auth()->user()->email : 'guest') .
                             ' viewed export page at ' . now()
        ]);
        $data = User::role('teacher')->get();
        //return $data;
        return view('export.index', compact('data'));
    }
}
