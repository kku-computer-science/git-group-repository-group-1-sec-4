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
        ActivityLog::create([
            'user_id'    => auth()->user()->id ?? null,
            'role'       => auth()->user()->roles->pluck('name')->first() ?? null,
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


        if (
            method_exists($this, 'hasTooManyLoginAttempts') &&
            $this->hasTooManyLoginAttempts($request)
        ) {
            /*$this->fireLockoutEvent($request);
            return $this->sendLockoutResponse($request);*/
            /*$key = $this->throttleKey($request);
                $rateLimiter = $this->limiter();
                
                $limit = [3 => 1, 5 => 5];
                $attempts = $rateLimiter->attempts($key);  // return how attapts already yet
                if (array_key_exists($attempts, $limit)) {
                    $this->decayMinutes = $limit[$attempts];
                }
                $this->incrementLoginAttempts($request);*/ // login สำเร็จ

            $this->fireLockoutEvent($request);
            
            return $this->sendLockoutResponse($request);
        }

        // $input = $request->all();
        // $this->validate($request, [
        //     'username' => 'required',
        //     'password' => 'required',
        // ]);

        // $fieldType = filter_var($request->username, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        // if(auth()->attempt(array($fieldType => $input['username'], 'password' => $input['password'])))
        // {
        //     return redirect()->route('dashboard');
        // }else{
        //     return redirect()->back()
        //                 ->withInput($request->all())
        //                 ->withErrors(['error' => 'Login Failed: Your user ID or password is incorrect']);
        // }

        $credentials = $request->only('username', 'password');
        $response = request('recaptcha');

        $data = [
            "username" => $credentials['username'],
            "password" => $credentials['password']
        ];

        $rules = [
            'username' => 'required',
            'password' => 'required'
        ];


        $validator = Validator::make($data, $rules);

        $input = $request->all();
        // $validator = $this->validate($request, [
        //     'username' => 'required',
        //     'password' => 'required',
        // ]);
        $fieldType = filter_var($request->username, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        if (!$validator->fails()) {
            $remember = $request->filled('remember');
            if (auth()->attempt([$fieldType => $input['username'], 'password' => $input['password']], $remember) /* && $this->checkValidGoogleRecaptchaV3($response) */) {

                ActivityLog::create([
                    'user_id' => auth()->id(),
                    'role'    => auth()->user()->roles->pluck('name')->first() ?? null,
                    'action'  => 'login',
                    'description' => 'User ' . auth()->user()->email . ' has logged in at ' . now() .
                        ($remember ? ' (Remember Me enabled)' : '')
                ]);

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
                ActivityLog::create([
                    'user_id'    => null,
                    'role'       => 'guest',
                    'action'     => 'login_failed',
                    'description' => 'Login failed for username=' . $request->username . ' at ' . now()
                ]);
                return redirect()->back()
                    ->withInput($request->all())
                    ->withErrors(['error' => 'Login Failed: Your user ID or password is incorrect']);
            }
        } else {
            // กรณี validate ฟอร์มไม่ผ่าน (เช่นไม่ได้กรอก username/password)
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
