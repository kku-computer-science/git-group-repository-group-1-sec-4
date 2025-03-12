<?php

namespace App\Http\Controllers;

use App\Models\Expertise;
use App\Models\Fund;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\ActivityLog;

class ExpertiseController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $id = auth()->user()->id;
        if (auth()->user()->hasRole('admin')) {
            $experts = Expertise::all();
        } else {
            $experts = Expertise::with('user')->whereHas('user', function ($query) use ($id) {
                $query->where('users.id', '=', $id);
            })->paginate(10);
        }

        return view('expertise.index', compact('experts'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('expertise.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $r = $request->validate([
            'expert_name' => 'required',
        ]);

        $exp_id = $request->exp_id;
        // หา record เดิม (ถ้ามี)
        $exp = Expertise::find($exp_id);

        // (3) เช็คเงื่อนไขการสร้าง/แก้ไข
        if (empty($exp_id)) {
            // ----- กรณี "สร้าง" -----
            if (auth()->user()->hasRole('admin')) {
                // admin สร้างได้โดยตรง
                $newExp = Expertise::create($request->all());

                // Log - CREATE
                ActivityLog::create([
                    'user_id'    => auth()->id(),
                    'role'       => auth()->user()->roles->pluck('name')->first() ?? null,
                    'action'     => 'create_expert',
                    'description' => 'User ' . auth()->user()->email . ' created new expert ID = ' . $newExp->id
                ]);
            } else {
                // user ธรรมดา สร้างผ่านความสัมพันธ์
                $user = User::find(Auth::user()->id);
                $createdModel = $user->expertise()->create([
                    'expert_name' => $request->expert_name
                ]);

                // Log - CREATE
                ActivityLog::create([
                    'user_id'    => auth()->id(),
                    'role'       => auth()->user()->roles->pluck('name')->first() ?? null,
                    'action'     => 'create_expert',
                    'description' => 'User ' . auth()->user()->email . ' created new expert ID = ' . $createdModel->id
                ]);
            }

            $msg = 'Expertise entry created successfully.';
        } else {
            // ----- กรณี "แก้ไข" -----
            if (auth()->user()->hasRole('admin')) {
                // admin แก้ไขโดยตรง
                if ($exp) {
                    $exp->update($request->all());

                    // Log - EDIT
                    ActivityLog::create([
                        'user_id'    => auth()->id(),
                        'role'       => auth()->user()->roles->pluck('name')->first() ?? null,
                        'action'     => 'edit_expert',
                        'description' => 'User ' . auth()->user()->email . ' edited expert ID = ' . $exp->id
                    ]);
                }
            } else {
                // user ธรรมดา แก้ไขผ่านความสัมพันธ์
                $user = User::find(Auth::user()->id);
                $updatedModel = $user->expertise()->updateOrCreate(
                    ['id' => $exp_id],
                    ['expert_name' => $request->expert_name]
                );

                // Log - EDIT
                ActivityLog::create([
                    'user_id'    => auth()->id(),
                    'role'       => auth()->user()->roles->pluck('name')->first() ?? null,
                    'action'     => 'edit_expert',
                    'description' => 'User ' . auth()->user()->email . ' edited expert ID = ' . $updatedModel->id
                ]);
            }

            $msg = 'Expertise data is updated successfully';
        }

        // สุดท้าย redirect / back
        if (auth()->user()->hasRole('admin')) {
            return redirect()->route('experts.index')->with('success', $msg);
        } else {
            return back()->withInput(['tab' => 'expertise']);
        }
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Expertise $expertise)
    {
        //return view('expertise.show',compact('expertise'));
        //$where = array('id' => $id);
        //$exp = Expertise::where($where)->first();
        return response()->json($expertise);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $where = array('id' => $id);
        $exp = Expertise::where($where)->first();
        return response()->json($exp);
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        // (4) ควรหาโมเดลก่อนลบ (ถ้าต้องการแสดงชื่อ หรือข้อมูลอื่น)
        $exp = Expertise::find($id);
        if ($exp) {
            // บันทึก Log ก่อนลบ
            ActivityLog::create([
                'user_id'    => auth()->id(),
                'role'       => auth()->user()->roles->pluck('name')->first() ?? null,
                'action'     => 'delete_expert',
                'description' => 'User ' . auth()->user()->email
                    . ' deleted expert ID = ' . $exp->id
                    . ' (name: ' . $exp->expert_name . ')'
            ]);

            // ลบ
            $exp->delete();
        } else {
            // กรณีหาไม่เจอ
            // อาจจะบันทึก Log ว่าลบไม่เจอ หรือข้ามไป
        }

        // ข้อความแจ้ง
        $msg = 'Expertise entry deleted successfully.';

        if (auth()->user()->hasRole('admin')) {
            return redirect()->route('experts.index')->with('success', $msg);
        } else {
            return back()->withInput(['tab' => 'expertise']);
        }
    }
}
