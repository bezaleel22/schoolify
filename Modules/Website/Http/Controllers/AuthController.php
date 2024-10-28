<?php

namespace Modules\Website\Http\Controllers;

use App\ApiBaseMethod;
use App\Scopes\StatusAcademicSchoolScope;
use App\SmAcademicYear;
use App\SmDateFormat;
use App\SmGeneralSettings;
use App\SmLanguage;
use App\SmStaff;
use App\SmStudent;
use App\SmStyle;
use App\SmUserLog;
use App\User;
use Jenssegers\Agent\Agent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Modules\Website\Traits\AuthTrait;
use Illuminate\Support\Facades\Session;
use Illuminate\Contracts\Support\Renderable;
use Modules\RolePermission\Entities\InfixModuleInfo;

class AuthController extends Controller
{
    use Authtrait;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function login(Request $request)
    {
        try {
            $credentials = $request->only('email', 'password');
            $users = User::where('email', $request->email)->get();
            if (!$users->count()) {
                $users = User::where('username', $request->email)->get();
            }
            if (!$users->count()) {
                $users = User::where('phone_number', $request->email)->get();
            }

            $user = null;

            return response()->json($users, 200);

            // Single User
            if (count($users) == 1) {
                $user = $users->first();
                $school = $user->school_id;

                if (Hash::check($request->password, $user->password)) {
                    if ($school->id != SmGeneralSettings::where('school_id', $school->id)->first()->school_id) {
                        if ($school->domain != 'school') {
                            $key = "DevelopedBySpondonit" . '-' . $request->email . '-' . $request->password;
                            $code = encrypt($key);

                            return response()->json([
                                'redirect_url' => '//' . $school->domain . '.' . config('app.short_url') . '/school-secret-login?code=' . $code . '&email=' . urlencode($request->email)
                            ]);
                        }
                    }

                    if (Auth::attempt($credentials)) {
                        if (Auth::user()->active_status == 0) {
                            Auth::logout();
                            return response()->json([
                                'error' => 'You are not allowed, Please contact the administrator.'
                            ], 403);
                        }

                        if (moduleStatusCheck('TwoFactorAuth') && SmGeneralSettings::where('school_id', $school)->first()->two_factor) {
                            $this->twoFactorAuth(auth()->user());
                        }
                    }
                }
                return response()->json(['error' => 'Invalid credentials.'], 401);
            }

            // Multiple Users
            if (count($users) > 1) {
                $schools = [];
                foreach ($users as $user) {
                    if (!$user->school->active_status) {
                        return response()->json([
                            'error' => 'Your Institution is not Approved, Please contact the administrator.'
                        ], 403);
                    }

                    if (Hash::check($request->password, $user->password)) {
                        $key = "DevelopedBySpondonit" . '-' . $request->email . '-' . $request->password;
                        $code = encrypt($key);

                        $schools[] = [
                            'domain' => $user->school->domain,
                            'url' => '//' . $user->school->domain . '.' . config('app.short_url') . '/school-secret-login?code=' . $code . '&email=' . urlencode($request->email)
                        ];
                    }
                }

                if (count($schools) == 1) {
                    return response()->json([
                        'redirect_url' => $schools[0]['url']
                    ]);
                } else {
                    return response()->json(['error' => 'Multiple schools found.'], 400);
                }
            }

            $school = app('school');
            $request->merge(['school_id' => $school->id]);
            $logged_in = false;
            $gs = SmGeneralSettings::where('school_id', $school->id)->first();
            session()->forget('generalSetting');
            session()->put('generalSetting', $gs);
            if ($school->id != 1 && $school->active_status != 1) {
                return response()->json([
                    'error' => 'Your Institution is not Approved, Please contact the administrator.'
                ], 403);
            }
            if (config('app.app_sync') && $request->auto_login) {
                $user = User::where('email', $request->email)->first();
                if ($user) {
                    $this->guard()->login($user);
                    $logged_in = Auth::check();
                }
            } else {
                $this->validateLogin($request);
                if ($this->hasTooManyLoginAttempts($request)) {
                    $this->fireLockoutEvent($request);
                    return $this->sendLockoutResponse($request);
                }

                $user = User::where('username', $request->email)->where('school_id', $school->id)->first();

                if (!$user) {
                    $user = User::where('phone_number', $request->email)->where('school_id', $school->id)->first();
                }
                if (!$user) {
                    $user = User::where('email', $request->email)->where('school_id', $school->id)->first();
                }

                if ($user) {
                    if (Hash::check($request->password, $user->password)) {
                        $this->guard()->login($user);
                        $logged_in = Auth::check();
                    }
                } else {
                    $logged_in = $this->attemptLogin($request);
                }
            }

            if ($logged_in) {
                if (!Auth::user()->access_status || !Auth::user()->active_status) {
                    $this->guard()->logout();
                    return response('You are not allowed, Please contact with administrator.', 'Failed');
                    return redirect()->route('login');
                }

                // System date format save in session
                $date_format_id = generalSetting()->date_format_id;
                $system_date_format = 'jS M, Y';
                if ($date_format_id) {
                    $system_date_format = SmDateFormat::where('id', $date_format_id)->first(['format'])->format;
                }

                session()->put('system_date_format', $system_date_format);

                // System academic session id in session

                $all_modules = [];
                $modules = InfixModuleInfo::select('name')->get();
                foreach ($modules as $module) {
                    $all_modules[] = $module->name;
                }

                session()->put('all_module', $all_modules);

                //Session put text decoration
                $ttl_rtl = generalSetting()->ttl_rtl;
                session()->put('text_direction', $ttl_rtl);

                $active_style = SmStyle::where('school_id', Auth::user()->school_id)->where('is_active', 1)->first();
                session()->put('active_style', $active_style);

                $all_styles = SmStyle::where('school_id', Auth::user()->school_id)->get();
                session()->put('all_styles', $all_styles);

                //Session put activeLanguage
                $systemLanguage = SmLanguage::where('school_id', Auth::user()->school_id)->get();
                session()->put('systemLanguage', $systemLanguage);
                //session put academic years


                $academic_years = Auth::check() ? SmAcademicYear::where('active_status', 1)->where('school_id', Auth::user()->school_id)->get() : '';

                session()->put('academic_years', $academic_years);
                //session put sessions and selected language


                if (Auth::user()->role_id == 2) {
                    $profile = SmStudent::where('user_id', Auth::id())->withOutGlobalScopes([StatusAcademicSchoolScope::class])->first();

                    session()->put('profile', @$profile->student_photo);
                    // $session_id = $profile ? $profile->academic_id : generalSetting()->session_id;
                    $session_id = generalSetting()->session_id;
                } else {
                    $profile = SmStaff::where('user_id', Auth::id())->first();
                    if ($profile) {
                        session()->put('profile', $profile->staff_photo);
                    }
                    // $session_id = $profile && $profile->academic_id ? $profile->academic_id : generalSetting()->session_id;
                    $session_id = generalSetting()->session_id;
                }


                if (!$session_id) {
                    $session = SmAcademicYear::where('school_id', Auth::user()->school_id)->where('active_status', 1)->first();
                } else {
                    $session = SmAcademicYear::find($session_id);
                }
                if (!$session) {
                    $session = SmAcademicYear::where('school_id', Auth::user()->school_id)->first();
                }

                session()->put('sessionId', $session->id);
                session()->put('session', $session);
                session()->put('school_config', generalSetting());

                $dashboard_background = DB::table('sm_background_settings')->where([['is_default', 1], ['title', 'Dashboard Background']])->first();
                session()->put('dashboard_background', $dashboard_background);

                $email_template = \App\SmsTemplate::where('school_id', Auth::user()->school_id)->first();
                session()->put('email_template', $email_template);

                session(['role_id' => Auth::user()->role_id]);
                $agent = new Agent();
                $user_log = new SmUserLog();
                $user_log->user_id = Auth::user()->id;
                $user_log->role_id = Auth::user()->role_id;
                $user_log->school_id = Auth::user()->school_id;
                $user_log->ip_address = $request->ip();
                if (moduleStatusCheck('University')) {
                    $user_log->un_academic_id = getAcademicid();
                } else {
                    $user_log->academic_id = getAcademicid() ?? 1;
                }
                $user_log->user_agent = $agent->browser() . ', ' . $agent->platform();
                $user_log->save();

                userStatusChange(auth()->user()->id, 1);

                if (moduleStatusCheck('TwoFactorAuth') && generalSetting()->two_factor) {
                    $this->twoFactorAuth(auth()->user());
                }

                $data = $this->sendLoginResponse($request);
                return ApiBaseMethod::sendResponse($data, 'Login successful.');
            }
        } catch (\Exception $e) {
            $this->incrementLoginAttempts($request);
            return ApiBaseMethod::sendError('Error.', $e->getMessage());
        }
    }

    //user logout method
    public function logout(Request $request)
    {
        $user = Auth::user();
        userStatusChange($user->id, 0);
        Session::flush();
        Auth::logout();
        return redirect()->route('login');
    }

    public function index(Request $request)
    {
        return response()->json(["message" => "Login successful"], 200);
    }
}
