<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App;
use App\Models\ResearchGroup;
use App\Models\ResearchProject;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;
use App\Models\ActivityLog;

class LocalizationController extends Controller
{
    public function index()
    {
        $resp = ResearchGroup:: all();
        return view('welcome',compact('resp'));
       // return view('welcome');
    }
    public function switchLang($lang)
    {
        if (array_key_exists($lang, Config::get('languages'))) {
            Session::put('applocale', $lang);
            ActivityLog::create([
                'user_id' => auth()->check() ? auth()->user()->id : null,
                'role'    => auth()->check() ? auth()->user()->roles->pluck('name')->first() : 'guest',
                'action'  => 'change_language',
                'description' => 'User ' . (auth()->check() ? auth()->user()->email : 'guest') .
                                 ' switched language to ' . $lang . ' at ' . now()
            ]);
        }
        return redirect()->back();
    }
}

