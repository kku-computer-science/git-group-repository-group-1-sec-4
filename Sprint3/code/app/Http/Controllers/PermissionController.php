<?php

namespace App\Http\Controllers;

use DB;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use App\Models\ActivityLog;

class PermissionController extends Controller
{
    /**
     * create a new instance of the class
     *
     * @return void
     */
    function __construct()
    {
        $this->middleware('permission:permission-list|permission-create|permission-edit|permission-delete', ['only' => ['index', 'store']]);
        $this->middleware('permission:permission-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:permission-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:permission-delete', ['only' => ['destroy']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $data = Permission::all();

        return view('permissions.index', compact('data'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('permissions.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|unique:permissions,name',
        ]);

        // สร้าง Permission
        $permission = Permission::create(['name' => $request->input('name')]);

        // 3) บันทึก Log การสร้าง Permission
        ActivityLog::create([
            'user_id'    => auth()->id(),
            'role'       => auth()->user()->roles->pluck('name')->first() ?? null,
            'action'     => 'create_permission',
            'description' => 'User ' . auth()->user()->email . ' created permission ID = ' . $permission->id
        ]);

        return redirect()->route('permissions.index')
            ->with('success', 'Permission created successfully.');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $permission = Permission::find($id);

        // 4) บันทึก Log การดูรายละเอียด (ถ้าต้องการ)
        ActivityLog::create([
            'user_id'    => auth()->id(),
            'role'       => auth()->user()->roles->pluck('name')->first() ?? null,
            'action'     => 'view_permission_detail',
            'description' => 'User ' . auth()->user()->email . ' viewed permission ID = ' . $id
        ]);

        return view('permissions.show', compact('permission'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $permission = Permission::find($id);

        return view('permissions.edit', compact('permission'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'required'
        ]);

        $permission = Permission::find($id);
        $permission->name = $request->input('name');
        $permission->save();

        ActivityLog::create([
            'user_id'    => auth()->id(),
            'role'       => auth()->user()->roles->pluck('name')->first() ?? null,
            'action'     => 'edit_permission',
            'description' => 'User ' . auth()->user()->email . ' edited permission ID = ' . $permission->id
        ]);

        return redirect()->route('permissions.index')
            ->with('success', 'Permission updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $permission = Permission::find($id);

        // 6) บันทึก Log การลบ Permission
        ActivityLog::create([
            'user_id'    => auth()->id(),
            'role'       => auth()->user()->roles->pluck('name')->first() ?? null,
            'action'     => 'delete_permission',
            'description' => 'User ' . auth()->user()->email . ' deleted permission ID = ' . $id
        ]);

        $permission->delete();

        return redirect()->route('permissions.index')
            ->with('success', 'Permission deleted successfully');
    }
}
