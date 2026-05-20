<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  @php($faviconPath = setting('favicon'))
  @if($faviconPath)
    <link rel="icon" type="image/png" href="{{ asset('storage/'.$faviconPath) }}">
  @endif
  <title>Cibato Commerce Admin Login</title>
  @vite(['resources/css/frontend/app.css'])
</head>
<body class="min-h-screen grid place-items-center" style="background:#f5f7fa">
  <div class="w-full max-w-md bg-white border border-gray-200 rounded-xl shadow-sm p-6">
    <h1 class="text-2xl font-semibold mb-4">Cibato Commerce Admin Login</h1>
    @if($errors->any())
      <div class="mb-3 text-sm text-red-600">{{ $errors->first() }}</div>
    @endif
    <form method="POST" action="{{ route('admin.login.post') }}" class="space-y-3">
      @csrf
      <input type="email" name="email" value="{{ old('email') }}" class="w-full border rounded px-3 py-2" placeholder="Email" required>
      <input type="password" name="password" class="w-full border rounded px-3 py-2" placeholder="Password" required>
      <label class="text-sm flex items-center gap-2"><input type="checkbox" name="remember" value="1">Remember me</label>
      <button class="w-full btn-primary">Login</button>
    </form>
  </div>
</body>
</html>
