<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\ActivityLog;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;
    use ThrottlesLogins;
    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;
    protected $maxAttempts = 10; // Default is 5
    protected $decayMinutes = 5; // Default is 1  //define 5 minute

    /**
     * Create a new controller instance.
     *
     * @return void
     */


    public function __construct()
    {

        $this->middleware(['guest'])->except('logout');
    }

    public function username()
    {
        return 'email';
    }

    public function logout(Request $request)
    {
        \App\Models\ActivityLog::create([
            'user_id'    => auth()->user()->id ?? null,
            'action'     => 'logout',
            'description' => 'User ' . auth()->user()->email . ' has logged out at ' . now()
        ]);
        $request->session()->flush();
        $request->session()->regenerate();
        Auth::logout();
        return redirect('/login');
    }

    protected function redirectTo()
    {
        if (Auth::user()->hasRole('admin')) {
            return route('dashboard');
        } elseif (Auth::user()->hasRole('staff')) {
            return route('dashboard');
        } elseif (Auth::user()->hasRole('teacher')) {
            return route('dashboard');
        } elseif (Auth::user()->hasRole('student')) {
            return route('dashboard');
            //return view('home');
        }
    }

    public function login(Request $request)
    {
        // ตรวจสอบการพยายามล็อกอิน
        if (
            method_exists($this, 'hasTooManyLoginAttempts') &&
            $this->hasTooManyLoginAttempts($request)
        ) {
            $this->fireLockoutEvent($request);
            return $this->sendLockoutResponse($request);
        }

        // กำหนดข้อมูลจากฟอร์ม
        $credentials = $request->only('username', 'password');
        $response = request('recaptcha');
        $remember = $request->has('remember');
        $data = [
            "username" => $credentials['username'],
            "password" => $credentials['password']
        ];

        // กำหนด validation rules
        $rules = [
            'username' => 'required',
            'password' => 'required'
        ];

        // สร้างตัวตรวจสอบ
        $validator = Validator::make($data, $rules);
        $input = $request->all();

        // ตรวจสอบว่า login ผ่านและ recaptcha ถูกต้อง
        $fieldType = filter_var($request->username, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        if (!$validator->fails()) {
            $rememberMe = $request->has('remember') ? true : false;

            if (auth()->attempt(array($fieldType => $input['username'], 'password' => $input['password']), $rememberMe) && $this->checkValidGoogleRecaptchaV3($response)) {
                // บันทึก activity log สำหรับ login
                ActivityLog::create([
                    'user_id'    => auth()->user()->id,
                    'action'     => 'login',
                    'description' => 'User ' . auth()->user()->email . ' logged in at ' . now()
                ]);

                // ถ้าผู้ใช้เลือก "Remember Me" ให้บันทึก activity log อีกอัน
                if ($request->has('remember')) {
                    ActivityLog::create([
                        'user_id'    => auth()->user()->id,
                        'action'     => 'remember_me',
                        'description'=> 'User ' . auth()->user()->email . ' chose Remember Me at ' . now()
                    ]);
                }
                

                // Redirect ตาม role
                if (Auth::user()->hasRole('admin')) {
                    return redirect()->route('dashboard');
                } elseif (Auth::user()->hasRole('student')) {
                    return redirect()->route('dashboard');
                } elseif (Auth::user()->hasRole('staff')) {
                    return redirect()->route('dashboard');
                } elseif (Auth::user()->hasRole('teacher')) {
                    return redirect()->route('dashboard');
                }
            } else {
                // ล็อกอินไม่ผ่าน
                $this->incrementLoginAttempts($request);
                return redirect()->back()
                    ->withInput($request->all())
                    ->withErrors(['error' => 'Login Failed: Your user ID or password is incorrect']);
            }
        } else {
            // กรณี validate ฟอร์มไม่ผ่าน
            return redirect('login')->withErrors($validator->errors())->withInput();
        }
    }




    public function checkValidGoogleRecaptchaV3($response)
    {
        $url = "https://www.google.com/recaptcha/api/siteverify";

        $data = [
            'secret' => "6Ldpye4ZAAAAAKwmjpgup8vWWRwzL9Sgx8mE782u",
            'response' => $response
        ];

        $options = [
            'http' => [
                'header' => 'Content-Type: application/x-www-form-urlencoded\r\n',
                'method' => 'POST',
                'content' => http_build_query($data)
            ]
        ];


        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        $resultJson = json_decode($result);

        return $resultJson->success;
    }
}
