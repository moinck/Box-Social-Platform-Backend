<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Email Verification</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', Arial, sans-serif;
            background-color: #f2f4f8;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 600px;
            margin: 40px auto;
            background-color: #ffffff;
            padding: 40px 30px;
            border-radius: 12px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.07);
        }

        .logo {
            text-align: center;
            margin-bottom: 24px;
        }

        .logo img {
            width: 100px;
            height: 100px;
        }

        .title {
            text-align: center;
            font-size: 26px;
            color: #222;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .subtitle {
            text-align: center;
            font-size: 16px;
            color: #555;
            margin-bottom: 32px;
        }

        .cta {
            text-align: center;
        }

        .cta a {
            background-color: #F4D106;
            color: #000;
            padding: 14px 32px;
            border-radius: 30px;
            text-decoration: none;
            font-weight: 600;
            font-size: 16px;
            display: inline-block;
            box-shadow: 0 3px 8px rgba(0, 0, 0, 0.1);
            transition: background-color 0.3s ease;
        }

        .cta a:hover {
            background-color: #e6c300;
        }

        .footer {
            text-align: center;
            font-size: 13px;
            color: #999;
            margin-top: 40px;
            line-height: 1.6;
        }

        @media (max-width: 600px) {
            .container {
                padding: 30px 20px;
            }
        }
    </style>
</head>

<body>
    @yield('main-content')
</body>

</html>