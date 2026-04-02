<!DOCTYPE html>
<html>
<head>
    <title>Customer Details</title>
</head>
<body>
    <h1>Customer Edit</h1>

    @if($errors->any())
        <ul style="color:red">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif
    <form action="{{route('customers.update',$customer->id)}}" method="POST"
        enctype="multipart/form-data" >
        @csrf
        @method('PUT')
        <label for="first_name">First Name</label>
        <input type="text" name="first_name" value={{ $customer->first_name }}>

        <label for="last_name">Last Name</label>
        <input type="text" name="last_name" value={{ $customer->last_name }}>

        <label for="email">email</label>
        <input type="text" name="email" value={{ $customer->email }}>

        <label for="phone">phone</label>
        <input type="text" name="phone" value={{ $customer->phone }}>

        <label for="address">address</label>
        <input type="text" name="address" value={{ $customer->address }}>

        <label for="id">date_of_birth</label>
        <input type="text" name="date_of_birth" value={{ $customer->date_of_birth }}>
        {{-- <a href="{{ route('customers.index') }}">Back to list</a> --}}
        <button type="submit">save</button>
    </form>


    {{-- <p><strong>ID:</strong> {{ $customer->id }}</p>
    <p><strong>Name:</strong> {{ $customer->first_name }} {{ $customer->second_name }} {{ $customer->last_name }}</p>
    <p><strong>Email:</strong> {{ $customer->email }}</p>
    <p><strong>Phone:</strong> {{ $customer->phone }}</p>
    <p><strong>Gender:</strong> {{ $customer->gender == 1 ? 'Male' : 'Female' }}</p>
    <p><strong>Address:</strong> {{ $customer->address }}</p>
    <p><strong>Date of Birth:</strong> {{ $customer->date_of_birth }}</p>
    <p><strong>Image:</strong><br>
        @if($customer->image_path)
            <img src="{{ asset($customer->image_path) }}" width="150" alt="Customer Image">
        @else
            No Image
        @endif
    </p> --}}


</body>
</html>
