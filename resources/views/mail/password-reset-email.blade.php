{{-- <!DOCTYPE html>
<html>

<head>
    <title>Verification Code</title>
</head>

<body>
    <h1>Hello, {{ $name }}!</h1>
    <p>Please use the following code to reset your password:</p>
    <h2>{{ $verificationCode }}</h2>
    <p>The code is valid for 5 minutes. If you didn't wan't to reset your password, please ignore this email.</p>
</body>

</html> --}}
<!DOCTYPE html>
<html lang="en" xmlns:th="http://www.w3.org/1999/xhtml">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Your Password</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #ffffff;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .header {
            text-align: center;
            padding-bottom: 20px;
            border-bottom: 2px solid #eeeeee;
        }

        .header h1 {
            margin: 0;
            color: #333333;
        }

        .content {
            margin: 20px 0;
            line-height: 1.6;
        }

        .code {
            font-size: 24px;
            font-weight: bold;
            color: #007bff;
            display: inline-block;
            padding: 10px;
            background-color: #f1f1f1;
            border-radius: 4px;
            border: 1px solid #dddddd;
        }

        .footer {
            text-align: center;
            padding-top: 20px;
            border-top: 2px solid #eeeeee;
            font-size: 14px;
            color: #777777;
        }

        .footer a {
            color: #007bff;
            text-decoration: none;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>Reset Your Password</h1>
        </div>
        <div class="content">
            <h2>
                <p>Dear {{ $name }},</p>
            </h2>
            <h4>
                <p>We received a request to reset your password for your account.
                    Your password will not be changed.</p>
                <p>To reset your password, please use the 6-digit confirmation code provided below:</p>
            </h4>
            <p class="code">{{ $verificationCode }}</p>
            <h4>
                <p>This code will expire in 5 minutes. If the code has expired, you will need to request a new password
                    reset.</p>
            </h4>
            {{-- <p>If you did not create an account with us, please ignore this email.</p> --}}
        </div>
        <div class="footer">
            <p>If you did not create an request for reset password, please ignore this email.</p>
        </div>
    </div>
</body>

</html>
