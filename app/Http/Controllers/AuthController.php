<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function loadRegister()
    {
        return view('register');
    }
    public function userRegister(RegisterRequest $request)
    {
        try {
            $this->userService->registerUser($request->validated());
            return redirect()->back()->with('success', 'Registration successful.');
        } catch (ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput();
        }
    }
    public function loadLogin()
    {
        return view('login');
    }

    public function userLogin(LoginRequest $request)
    {
        try {
            // Pass the validated data to the service layer for login
            $this->userService->loginUser($request->validated());

            // Redirect to the dashboard on successful login
            return redirect('/dashboard')->with('success', 'Login successful.');
        } catch (ValidationException $e) {
            // Redirect back with validation errors
            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput();
        } catch (\Exception $e) {
            // Catch any other exception and return a generic error message
            return redirect()->back()
                ->withErrors(['error' => 'An unexpected error occurred. Please try again later.'])
                ->withInput();
        }
    }

    public function dashboard()
    {
        return view('dashboard');
    }

    public function logout(Request $request)
    {
        try {
            $request->session()->flush();
            Auth::logout();
            return response()->json(['success' => true]);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred during logout.',
                'error' => $th->getMessage(),
            ], 500);
        }
    }
}
