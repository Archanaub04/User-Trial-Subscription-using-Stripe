@extends('layouts.auth-layout')
@section('content')
    <form action="{{ route('userRegister') }}" method="post">
        @csrf

        @if (session('success'))
            <div class="alert alert-success">
                <p style="color: green;">{{ session('success') }}</p>
            </div>
        @endif

        <h1>Sign Up</h1>
        <fieldset>

            <div>
                <label for="name">Name:</label>
                <input type="text" id="name" name="user_name" required>
                @error('user_name')
                    <span class="text-danger" style="color: red">{{ $message }}</span>
                @enderror
            </div>
            <div>
                <label for="mail">Email:</label>
                <input type="email" id="mail" name="user_email" required>
                @error('user_email')
                    <span class="text-danger" style="color: red">{{ $message }}</span>
                @enderror
            </div>
            <div>
                <label for="password">Password:</label>
                <input type="password" id="password" name="user_password" required>
                @error('user_password')
                    <span class="text-danger" style="color: red">{{ $message }}</span>
                @enderror
            </div>

        </fieldset>
        <button type="submit">Sign Up</button>
    </form>
@endsection
