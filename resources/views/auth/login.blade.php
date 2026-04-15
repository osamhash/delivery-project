<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>

    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
</head>
<body>
    <div class="login-container">
        <h1>Login</h1>
        <form action="{{ route('login.submit') }}" method="POST">
            @csrf
            <label>Role:</label>
            <select name="role">
                <option value="">Choose Role</option>
                <option value="customer">Customer</option>
                <option value="provider">Provider</option>
                <option value="driver">Driver</option>
                <option value="admin">Admin</option>
            </select>
            @error('role')<span>{{ $message }}</span>@enderror

            <label>Email:</label>
            <input type="email" name="email" value="{{ old('email') }}">
            @error('email')
                <span class="error">{{ $message }}</span>
            @enderror

            <label>Password:</label>
            <input type="password" name="password">
            @error('password')
                <span class="error">{{ $message }}</span>
            @enderror
            @if(session('error'))
                <div class="error">{{ session('error') }}</div>
            @endif
            <button type="submit">Login</button>


    </div>
</body>
</html>

{{-- --------------------------------- --}}

 {{-- <label>Email:</label>
            <input type="email" name="email" value="{{ old('email') }}">
            @error('email')<span>{{ $message }}</span>@enderror

            <label>Password:</label>
            <input type="password" name="password">
            @error('password')<span>{{ $message }}</span>@enderror

            <button type="submit">Login</button>
        </form>

        @if(session('error'))
            <div style="color:red; margin-top:10px;">{{ session('error') }}</div>
        @endif --}}


