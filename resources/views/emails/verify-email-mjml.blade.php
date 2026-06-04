<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Verify Email</title>

    <style>
        body { margin:0; padding:0; background:#f1f1f1; font-family:Lato, Arial; }
        a { text-decoration:none; }
    </style>
</head>

<body>

<div style="max-width:600px;margin:auto;background:#fff;">

    {{-- HEADER --}}
    <div style="padding:30px 0;text-align:center;">
        <div style="font-size:22px;font-weight:700;">
            {{ $appName }}
        </div>
    </div>

    {{-- HERO IMAGE --}}
    <div style="text-align:center;padding:10px 0 20px;">
        <img src="{{ asset('images/email.png') }}" width="300">
    </div>

    {{-- TITLE --}}
    <div style="text-align:center;padding:10px 30px;">
        <h2 style="margin:0;font-size:28px;">
            Tolong Verifikasi Email Anda
        </h2>

        <p style="color:#666;font-size:16px;margin-top:10px;">
            Selamat datang {{ $user->name ?? '' }}, klik tombol di bawah untuk memverifikasi email Anda.
        </p>
    </div>

    {{-- BUTTON --}}
    <div style="text-align:center;padding:20px 0 40px;">
        <a href="{{ $verifyUrl }}"
        style="background:#30e3ca;color:#fff;padding:12px 32px;
                border-radius:30px;display:inline-block;">
            Verifikasi Email
        </a>
    </div>

    {{-- FOOTER --}}
    <div style="background:#fafafa;padding:30px;text-align:center;font-size:12px;color:#888;">
        Jika Anda tidak meminta email ini, abaikan saja.
    </div>

</div>

</body>
</html>