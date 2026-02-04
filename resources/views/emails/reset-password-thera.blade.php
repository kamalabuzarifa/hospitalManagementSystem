<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<body style="background-color:#f1f5f9;font-family:Inter,sans-serif;padding:30px">

<div style="max-width:520px;margin:auto;background:#ffffff;border-radius:24px;padding:32px">

<div style="text-align:center;margin-bottom:24px">
    <img src="{{ asset('images/hospital-logo.png') }}" width="48" alt="Thera" />
    <h2 style="margin:8px 0;color:#2b66f1">Thera</h2>
    <p style="color:#64748b">Therapeutic Smart Hospital System</p>
</div>

<h3 style="color:#0f172a">
{{ app()->getLocale() === 'ar' ? 'إعادة تعيين كلمة المرور' : 'Reset your password' }}
</h3>

<p style="color:#475569;font-size:14px">
{{ app()->getLocale() === 'ar'
    ? 'تم طلب إعادة تعيين كلمة المرور لحسابك'
    : 'We received a request to reset your account password'
}}
</p>

<div style="text-align:center;margin:32px 0">
    <a
        href="{{ $url }}"
        style="background:#2b66f1;color:#fff;padding:14px 28px;border-radius:14px;text-decoration:none;font-weight:600;display:inline-block"
    >
        {{ app()->getLocale() === 'ar' ? 'إعادة التعيين' : 'Reset Password' }}
    </a>
</div>

<p style="font-size:12px;color:#64748b">
{{ app()->getLocale() === 'ar'
    ? 'ينتهي الرابط خلال 60 دقيقة'
    : 'This link expires in 60 minutes'
}}
</p>

<hr style="margin:24px 0">

<p style="font-size:11px;color:#94a3b8;text-align:center">
© 2025 Thera System
</p>

</div>
</body>
</html>
