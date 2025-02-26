<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Program;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use App\Models\ActivityLog;

class DepartmentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    function __construct()
    {
        $this->middleware('permission:departments-list|departments-create|departments-edit|departments-delete', ['only' => ['index', 'store']]);
        $this->middleware('permission:departments-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:departments-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:departments-delete', ['only' => ['destroy']]);
        //Redirect::to('dashboard')->send();
    }

    public function index(Request $request)
    {
        $data = Department::latest()->paginate(5);

        return view('departments.index', compact('data'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('departments.create');
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
            'department_name_th' => 'required',
            'department_name_th' => 'required',
        ]);

        $input = $request->except(['_token']);
        $department = Department::create($input);

        // 3) บันทึก Log การสร้าง Department
        ActivityLog::create([
            'user_id'    => auth()->id(),
            'role'       => auth()->user()->roles->pluck('name')->first() ?? null,
            'action'     => 'create_department',
            'description' => 'User ' . auth()->user()->email . ' created department ID = ' . $department->id
        ]);

        return redirect()->route('departments.index')
            ->with('success', 'departments created successfully.');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Department $department)
    {
        // 4) บันทึก Log การดูรายละเอียด Department (ถ้าต้องการ)
        ActivityLog::create([
            'user_id'    => auth()->id(),
            'role'       => auth()->user()->roles->pluck('name')->first() ?? null,
            'action'     => 'view_department_detail',
            'description' => 'User ' . auth()->user()->email . ' viewed department ID = ' . $department->id
        ]);

        return view('departments.show', compact('department'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Department $department)
    {
        $department = Department::find($department->id);

        return view('departments.edit', compact('department'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Department $department)
    {
        $department->update($request->all());

        // 5) บันทึก Log การแก้ไข Department
        ActivityLog::create([
            'user_id'    => auth()->id(),
            'role'       => auth()->user()->roles->pluck('name')->first() ?? null,
            'action'     => 'edit_department',
            'description' => 'User ' . auth()->user()->email . ' edited department ID = ' . $department->id
        ]);

        return redirect()->route('departments.index')
            ->with('success', 'Department updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Department $department)
    {
        // 6) บันทึก Log การลบ Department
        ActivityLog::create([
            'user_id'    => auth()->id(),
            'role'       => auth()->user()->roles->pluck('name')->first() ?? null,
            'action'     => 'delete_department',
            'description' => 'User ' . auth()->user()->email . ' deleted department ID = ' . $department->id
        ]);

        $department->delete();
        return redirect()->route('departments.index')
            ->with('success', 'Department delete successfully');
    }
}
