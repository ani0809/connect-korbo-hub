<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ translate('Error') }} 500</title>
    <style>
        body { margin:0; font-family: Arial, sans-serif; background:#f5f6f8; color:#212529; }
        .wrap { min-height:100vh; display:flex; align-items:center; justify-content:center; text-align:center; padding:24px; }
        .box { max-width:760px; width:100%; }
        .box img { width:100%; max-width:720px; height:auto; }
        .msg { margin-top:18px; font-size:16px; color:#6b7280; }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="box">
            <img src="{{ static_asset('assets/img/error-custom.png') }}" alt="error image" onerror="this.onerror=null;this.src='{{ static_asset('assets/img/500.svg') }}';">
            <p class="msg">{{ translate('Something went wrong!') }} (500)</p>
        </div>
    </div>
</body>
</html>
