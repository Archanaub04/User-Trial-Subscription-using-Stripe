@extends('layouts.auth-layout')
@section('content')
    <form action="{{ route('userLogin') }}" method="post">
        @csrf

        <h1>Sign In</h1>

        @if (session('error'))
            <div class="alert alert-danger" style="color: red;">
                <p>{{ session('error') }}</p>
            </div>
        @endif

        <fieldset>
            <div>
                <label for="mail">Email:</label>
                <input type="email" id="mail" name="email" value="{{ old('email') }}" required>
                @error('email')
                    <span class="text-danger" style="color: red">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
                @error('password')
                    <span class="text-danger" style="color: red">{{ $message }}</span>
                @enderror
            </div>
        </fieldset>

        <button type="submit">Sign In</button>
    </form>
@endsection
