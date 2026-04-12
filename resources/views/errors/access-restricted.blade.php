<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ session('direction', 'ltr') }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }}</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: linear-gradient(180deg, #f7f7f9 0%, #eceff3 100%);
            color: #1f2937;
        }
        .wrap {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }
        .card {
            width: min(640px, 100%);
            background: #ffffff;
            border-radius: 18px;
            box-shadow: 0 20px 60px rgba(15, 23, 42, 0.12);
            padding: 40px 32px;
            text-align: center;
        }
        .code {
            display: inline-block;
            padding: 8px 12px;
            border-radius: 999px;
            background: #fff4e5;
            color: #b45309;
            font-size: 14px;
            font-weight: 700;
            letter-spacing: 0.04em;
        }
        h1 {
            margin: 18px 0 12px;
            font-size: 34px;
            line-height: 1.2;
        }
        p {
            margin: 0 auto;
            max-width: 48ch;
            color: #4b5563;
            font-size: 16px;
            line-height: 1.7;
        }
        .footer-note {
            margin-top: 24px;
            color: #6b7280;
            font-size: 14px;
        }
    </style>
</head>
<body>
<div class="wrap">
    <div class="card">
        <div class="code">403</div>
        <h1>{{ $heading }}</h1>
        <p>{{ $message }}</p>
        <div class="footer-note">{{ $footerNote }}</div>
    </div>
</div>
</body>
</html>
