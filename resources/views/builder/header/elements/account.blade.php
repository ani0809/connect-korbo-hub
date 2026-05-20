@auth<div class="el-account"><span>{{ auth()->user()->name }}</span></div>@else<div class="el-account"><a href="/login">Login</a> / <a href="/register">Register</a></div>@endauth
