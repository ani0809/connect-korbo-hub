<!doctype html>
<html>
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width,initial-scale=1" />
<title>Installer</title>
<style>
body{font-family:Arial,Helvetica,sans-serif;background:#f4f7fb;margin:0}.wrap{max-width:1100px;margin:20px auto;display:grid;grid-template-columns:260px 1fr;gap:16px}.side,.main{background:#fff;border-radius:10px;box-shadow:0 2px 8px rgba(0,0,0,.08)}.side{padding:16px}.main{padding:20px}.step{padding:8px 10px;border-radius:6px;margin-bottom:6px}.active{background:#2563eb;color:#fff}.bar{height:8px;background:#e5e7eb;border-radius:4px;overflow:hidden}.fill{height:8px;background:#16a34a}.btn{background:#2563eb;color:#fff;border:none;padding:10px 14px;border-radius:6px;cursor:pointer}.btn2{background:#16a34a}.grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:12px}.card{border:1px solid #d1d5db;border-radius:8px;padding:12px}
@media(max-width:900px){.wrap{grid-template-columns:1fr}}
</style>
</head>
<body>
<div class="wrap">
  <aside class="side">
    <h3>Installation Steps</h3>
    @php($steps=[1=>'Welcome',2=>'Requirements',3=>'Permissions',4=>'Database',5=>'Migration',6=>'Setup',7=>'License',8=>'Demo',9=>'Finish'])
    <div class="bar"><div class="fill" style="width:{{ ($step/9)*100 }}%"></div></div>
    <div style="margin-top:12px">
      @foreach($steps as $i=>$name)
      <div class="step {{ $step===$i ? 'active' : '' }}">{{ $i }}. {{ $name }}</div>
      @endforeach
    </div>
  </aside>
  <main class="main">@yield('content')</main>
</div>
</body>
</html>
