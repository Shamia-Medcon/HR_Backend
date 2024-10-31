<!DOCTYPE html>
<html>
<head>
    <title>Password Confirmation Form</title>
    <style>
        /* Simple styling for the form */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
        }

        .container {
            height: 100vh;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #ccc;
            overflow: hidden;
        }

        .form {
            max-width: 400px;
            margin: 0 auto;
            border: 1px solid #ccc;
            padding: 20px;
            border-radius: 5px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        input[type="password"],
        input[type="submit"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 3px;
            border: 1px solid #ccc;
            box-sizing: border-box;
        }

        input[type="submit"] {
            background-color: #007bff;
            color: white;
            cursor: pointer;
        }

        input[type="submit"]:hover {
            background-color: #0056b3;
        }

        img.logo {
            max-width: 100px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="form">
        <img class="logo" src="{{asset('public/logo.webp')}}" alt="Logo">
        @if($errors->any())
            <p style="color:red">{{$errors->first()}}</p>
        @endif
        <form action="{{route('set.password',['id'=>$user->id])}}" method="post" id="passwordForm">
            @csrf
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>
            <label for="password_confirmation">Confirm Password:</label>
            <input type="password" id="password_confirmation" name="password_confirmation" required>
            <input type="submit" value="Submit">
        </form>
    </div>
</div>

</body>
</html>
