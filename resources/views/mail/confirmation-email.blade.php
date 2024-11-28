<!DOCTYPE html>
<html>

<head>
    <title>Verification Code</title>
</head>

<body>
    <h1>Hello, {{ $name }}!</h1>
    <p>Thank you for registering. Please use the following code to verify your email:</p>
    <h2>{{ $verificationCode }}</h2>
    <p>The code is valid for 5 minutes. If you didn't register, please ignore this email.</p>
</body>

</html>
