

<!-- TEST CUSTOM MAIL -->
@php
    $locale = app()->getLocale();
    $isArabic = $locale === 'ar';
@endphp

<!DOCTYPE html>
<html lang="{{ $isArabic ? 'ar' : 'en' }}" dir="{{ $isArabic ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.verify_email_notice') }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', Arial, sans-serif;
            background: #eaf2fb url('{{ asset('images/background-logo.png') }}') no-repeat center center;
            background-size: cover;
            margin: 0;
            padding: 0;
            min-height: 100vh;
        }
        .container {
            max-width: 420px;
            margin: 48px auto;
            background: #fff;
            border-radius: 2rem;
            box-shadow: 0 8px 32px 0 rgba(43,102,241,0.13);
            padding: 2.5rem 2rem 2rem 2rem;
            text-align: center;
        }
        .logo {
            width: 48px;
            height: 48px;
            margin-bottom: 1rem;
        }
        .brand {
            font-size: 1.8rem;
            font-weight: bold;
            color: #2b66f1;
            margin-bottom: 0.25rem;
            letter-spacing: 0.5px;
        }
        .subtitle {
            color: #64748b;
            font-size: 1rem;
            margin-bottom: 1.2rem;
        }
        .title {
            font-size: 1.15rem;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 1.5rem;
        }
        .btn {
            display: inline-block;
            background: #2b66f1;
            color: #fff;
            font-weight: 600;
            padding: 0.75rem 2.5rem;
            border-radius: 0.75rem;
            text-decoration: none;
            font-size: 1rem;
            margin: 1.5rem 0 1rem 0;
            box-shadow: 0 2px 8px rgba(43,102,241,0.10);
            transition: background 0.2s;
        }
        .btn:hover {
            background: #1d4ed8;
        }
        .footer {
            color: #94a3b8;
            font-size: 0.85rem;
            margin-top: 2rem;
            border-top: 1px solid #e5e7eb;
            padding-top: 1rem;
        }
        .code-link {
            word-break: break-all;
            color: #2b66f1;
            font-size: 0.95rem;
            background: #f3f6fb;
            border-radius: 0.5rem;
            padding: 0.3rem 0.6rem;
            display: inline-block;
            margin-top: 0.5rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="brand">{{ __('messages.thera') }}</div>
        <div class="subtitle">{{ __('messages.subtitle') }}</div>
        <div class="title">{{ __('messages.verify_email_notice') }}</div>
        <a href="{{ $actionUrl }}" class="btn">{{ __('messages.verify_email_btn', ['app' => config('app.name')]) }}</a>
        <div class="footer">
            {{ __('messages.verify_email_footer') }}<br>
            <span class="code-link">{{ $actionUrl }}</span>
        </div>
    </div>
</body>
</html>
