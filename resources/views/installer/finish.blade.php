@extends('installer.layout')
@section('content')
<h1 style="color:#16a34a">? Installation Complete!</h1>
<p>Admin URL: /admin</p><p>Admin Email: {{ $admin_email }}</p>
<a class="btn" href="/admin">Go to Admin Panel</a> <a class="btn btn2" href="/">Visit Store</a>
<div style="margin-top:20px;font-size:32px;animation:b 1s infinite">??</div><style>@keyframes b{0%{transform:scale(1)}50%{transform:scale(1.2)}100%{transform:scale(1)}}</style>
@endsection
