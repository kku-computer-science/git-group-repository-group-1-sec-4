<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Exports\UsersExport;

use App\Imports\UsersImport;
use App\Models\ActivityLog;
use Maatwebsite\Excel\Facades\Excel;

use App\Models\User;
use Spatie\Permission\Models\Role;
class ImportExportController extends Controller
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function index()
    {
       $roles = Role::pluck('name','name')->all();
       return view('users.import',compact('roles'));
    }
   
    /**
    * @return \Illuminate\Support\Collection
    */
    public function import(Request $request) 
    {
        $validatedData = $request->validate([

           'file' => 'required',

        ]);

        Excel::import(new UsersImport,$request->file('file'));
        ActivityLog::create([
            'user_id'    => auth()->id(),
            'role'       => auth()->user()->roles->pluck('name')->first() ?? null,
            'action'     => 'import_user',
            'description'=> 'User ' . auth()->user()->email 
                . ' imported users from file: ' 
                . $request->file('file')->getClientOriginalName()
        ]);
        return redirect('importfiles')->with('status', 'The file has been imported in Systems');
        
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function export($slug) 
    {
        return Excel::download(new UsersExport, 'users.'.$slug);
    }
   
}