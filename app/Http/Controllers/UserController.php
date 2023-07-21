<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;



class UserController extends Controller
{
    public function index()
    {
        return view('auth.login');
    }


    public function loginUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|max:100',
            'password' => 'required|min:6|max:50'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'messages' => $validator->getMessageBag()
            ]);
        } else {
            $user = User::where('email', $request->email)->first();
            if ($user) {
                if (Hash::check($request->password, $user->password)) {
                    $request->session()->put('LoggedInUser', $user->id);
                    return response()->json([
                        'status' => 200,
                        'messages' => 'success'
                    ]);
                } else {
                    return response()->json([
                        'status' => 401,
                        'messages' => 'Email or password is incorrect'
                    ]);
                }
            } else {
                return response()->json([
                    'status' => 401,
                    'messages' => 'User not found'
                ]);
            }
        }
    }


    public function register()
    {
        if (session()->has('LoggedInUser')) {
            return redirect('/profile');
        } else {
            return view('auth.register');
        }
    }



    public function saveUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:50',
            'email' => 'required|email|unique:users|max:100',
            'password' => 'required|min:6|max:50',
            'cpassword' => 'required|min:6|same:password'
        ], [
            'cpassword.same' => "Password did not matched",
            'cpassword.required' => 'Confirm password is required!'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'messages' => $validator->getMessageBag()
            ]);
        } else {
            $user = new User();
            $user->name = $request->name;
            $user->email = $request->email;
            $user->password = Hash::make($request->password);
            $user->save();

            return response()->json([
                'status' => 200,
                'messages' => 'Registered Successfully'
            ]);
        }
    }


    public function forgot()
    {
        return view('auth.forgot');
    }



    public function reset()
    {
        return view('auth.reset');
    }




    public function profile()
    {
        $data = ['userInfo' => DB::table('users')->where('id', session('LoggedInUser')
        )->first()];
        return view('profile', $data);
    }



    public function profileImageUpdate(Request $request)
    {
        print_r($_FILES);
        print_r($_POST);
    }



    public function logout()
    {
        if (session()->has('LoggedInUser')) {
            session()->pull('LoggedInUser');
            return redirect('/');
        }
    }
}
