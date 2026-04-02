<!DOCTYPE html>
<html>
<head>
    <title>Create Customer</title>
</head>
<body>
    <h1>Create New Customer</h1>

    @if($errors->any())
        <ul style="color:red">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif

    <form action="{{ route('customers.store') }}" method="POST">
        @csrf
        <label>First Name:</label><br>
        <input type="text" name="first_name" value="{{ old('first_name') }}"><br><br>

        <label>Second Name:</label><br>
        <input type="text" name="second_name" value="{{ old('second_name') }}"><br><br>

        <label>Last Name:</label><br>
        <input type="text" name="last_name" value="{{ old('last_name') }}"><br><br>

        <label>Email:</label><br>
        <input type="email" name="email" value="{{ old('email') }}"><br><br>

        <label>Password:</label><br>
        <input type="password" name="password"><br><br>

        <label>Phone:</label><br>
        <input type="text" name="phone" value="{{ old('phone') }}"><br><br>

        <label>Gender:</label><br>
        <select name="gender">
            <option value="1" {{ old('gender')==1?'selected':'' }}>Male</option>
            <option value="0" {{ old('gender')==0?'selected':'' }}>Female</option>
        </select><br><br>

        <label>Address:</label><br>
        <input type="text" name="address" value="{{ old('address') }}"><br><br>

        <label>Image Path:</label><br>
        <input type="text" name="image_path" value="{{ old('image_path') }}"><br><br>

        <label>Date of Birth:</label><br>
        <input type="date" name="date_of_birth" value="{{ old('date_of_birth') }}"><br><br>

        <button type="submit">Create Customer</button>
    </form>

    <a href="{{ route('customers.index') }}">Back to list</a>
</body>
</html>
