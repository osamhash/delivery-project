<!DOCTYPE html>
<html>
<head>
    <title>Customer Details</title>
</head>
<body>
    <h1>Customer Details</h1>

    <p><strong>ID:</strong> {{ $customer->id }}</p>
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
    </p>

    <a href="{{ route('customers.index') }}">Back to list</a>
</body>
</html>
