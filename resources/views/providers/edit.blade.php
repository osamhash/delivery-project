<!DOCTYPE html>
<html>
<head>
    <title>provider Details</title>
</head>
<body>
    <h1>provider Edit</h1>

    @if($errors->any())
        <ul style="color:#ff0000">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif
    <form action="{{route('providers.update',$provider->id)}}" method="POST"
        enctype="multipart/form-data" >
        @csrf
        @method('PUT')
        <label for="first_name">First Name</label>
        <input type="text" name="first_name" value={{ $provider->first_name }}>

        <label for="second_name">second Name</label>
        <input type="text" name="second_name" value={{ $provider->second_name }}>

        <label for="last_name">Last Name</label>
        <input type="text" name="last_name" value={{ $provider->last_name }}>

        <label for="email">email</label>
        <input type="text" name="email" value={{ $provider->email }}>

        <label for="phone">phone</label>
        <input type="text" name="phone" value={{ $provider->phone }}>

        <label for="address">address</label>
        <input type="text" name="address" value={{ $provider->address }}>

        <label for="id">date_of_birth</label>
        <input type="text" name="date_of_birth" value={{ $provider->date_of_birth }}>

        <label for="id">type</label>
        <input type="text" name="type" value={{ $provider->type }}>



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
