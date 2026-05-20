<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>{{ $subject ?? setting('site_name','Cibato Commerce') }}</title></head>
<body style="margin:0;padding:0;background:#f3f4f6;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;color:#374151;">
  <div style="max-width:600px;margin:0 auto;padding:20px;">
    <div style="background:{{ setting('primary_color','#2563eb') }};padding:30px;text-align:center;border-radius:12px 12px 0 0;">@if(setting('site_logo'))<img src="{{ asset('storage/'.setting('site_logo')) }}" alt="{{ setting('site_name','Cibato Commerce') }}" style="max-height:50px;max-width:200px;">@else<h2 style="color:#fff;margin:0;">{{ setting('site_name','Cibato Commerce') }}</h2>@endif</div>
    <div style="background:#fff;padding:40px;">@yield('content')</div>
    <div style="background:#1e293b;padding:25px 30px;text-align:center;border-radius:0 0 12px 12px;color:#94a3b8;font-size:13px;">
      <p style="margin:0 0 8px;">� {{ date('Y') }} {{ setting('site_name','Cibato Commerce') }}. All rights reserved.</p>
      <p style="margin:0 0 8px;">{{ setting('address') }}</p>
      <p style="margin:0;"><a href="{{ url('/') }}" style="color:#94a3b8;">Visit Store</a> | <a href="{{ url('/contact') }}" style="color:#94a3b8;">Contact</a> | <a href="{{ url('/privacy-policy') }}" style="color:#94a3b8;">Privacy</a></p>
      @yield('unsubscribe')
    </div>
  </div>
</body>
</html>
