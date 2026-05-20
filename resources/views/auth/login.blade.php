@extends('frontend.layouts.app')
@section('title', 'Login')
@section('content')
<div class="auth-page">
  <div class="auth-container">
    <div class="auth-card">
      <h1 class="auth-title">Welcome Back!</h1>
      <p class="auth-subtitle">Sign in to your account</p>
      @if(session('error'))<div class="alert-error">{{ session('error') }}</div>@endif
      @if(session('success'))<div class="alert-success">{{ session('success') }}</div>@endif
      <div class="auth-tabs" x-data="{ tab: 'email' }">
        <div class="auth-tab-buttons">
          <button @click="tab='email'" :class="{ 'active': tab==='email' }" class="auth-tab-btn">📧 Email</button>
          @if(feature('otp_login'))
          <button @click="tab='phone'" :class="{ 'active': tab==='phone' }" class="auth-tab-btn">📱 Phone / OTP</button>
          @endif
        </div>
        <div x-show="tab === 'email'" x-transition>
          <form method="POST" action="{{ route('login.post') }}">
            @csrf
            <div class="form-group"><label class="form-label">Email Address</label><input type="email" name="email" value="{{ old('email') }}" class="form-input {{ $errors->has('email') ? 'error' : '' }}" placeholder="you@example.com" required></div>
            <div class="form-group">
              <label class="form-label">Password <a href="{{ route('password.request') }}" class="forgot-link">Forgot password?</a></label>
              <div class="password-input-group" x-data="{ show:false }">
                <input :type="show ? 'text' : 'password'" name="password" class="form-input" placeholder="Your password" required>
                <button type="button" @click="show = !show" class="password-toggle"><span x-text="show ? '🙈' : '👁️'"></span></button>
              </div>
            </div>
            <div class="form-group"><label class="checkbox-label"><input type="checkbox" name="remember"><span>Remember me</span></label></div>
            <button type="submit" class="btn-auth-submit">Sign In</button>
          </form>
        </div>
        @if(feature('otp_login'))
        <div x-show="tab === 'phone'" x-transition x-data="OtpLogin()">
          <div x-show="step===1">
            <div class="form-group"><label class="form-label">Phone Number</label><div class="phone-input-group"><div class="phone-prefix">🇧🇩 +880</div><input type="tel" x-model="phone" class="form-input" placeholder="01XXXXXXXXX" @keyup.enter="sendOtp()" maxlength="11"></div></div>
            <div class="alert-error" x-show="error" x-text="error"></div>
            <button @click="sendOtp()" class="btn-auth-submit" :disabled="loading || phone.length < 11"><span x-show="!loading">Send OTP →</span><span x-show="loading">Sending...</span></button>
          </div>
          <div x-show="step===2">
            <div class="otp-sent-info"><div class="otp-sent-icon">📱</div><div><div class="otp-sent-title">OTP Sent!</div><div class="otp-sent-sub">Sent to +880 <span x-text="phone"></span></div></div></div>
            <div class="form-group">
              <label class="form-label">Enter 6-Digit OTP</label>
              <div class="otp-input-row" x-data="OtpInputs('otp', 'otp-combined')">
                <template x-for="i in 6" :key="i"><input type="text" inputmode="numeric" pattern="[0-9]" maxlength="1" class="otp-box" :id="'otp-'+i" @input="handleInput($event, i)" @keydown="handleKeydown($event, i)" @paste="handlePaste($event)" x-model="digits[i-1]"></template>
              </div>
              <input type="hidden" x-model="otpCode" id="otp-combined">
            </div>
            <div class="otp-timer"><span x-show="timer>0">Resend in <span class="timer-count" x-text="timer + 's'"></span></span><button type="button" x-show="timer===0" @click="sendOtp()" class="resend-btn">🔄 Resend OTP</button></div>
            <div class="alert-error" x-show="error" x-text="error"></div>
            <button @click="verifyOtp()" class="btn-auth-submit" :disabled="otpCode.length < 6 || loading"><span x-show="!loading">✅ Verify & Login</span><span x-show="loading">Verifying...</span></button>
            <button type="button" @click="step=1;error=null" class="btn-change-phone">← Change number</button>
          </div>
        </div>
        @endif
      </div>
      @include('auth.partials.social-login-buttons')
      <p class="auth-switch">Don't have an account? <a href="{{ route('register') }}">Create one →</a></p>
    </div>
  </div>
</div>
<script>
function OtpLogin(){return{step:1,phone:'',otpCode:'',loading:false,error:null,success:null,timer:0,timerInterval:null,async sendOtp(){if(this.phone.length<11){this.error='Please enter valid phone number';return;}this.loading=true;this.error=null;try{const resp=await fetch('/auth/otp/send',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]')?.content},body:JSON.stringify({phone:this.phone,type:'login'})});const data=await resp.json();if(data.success){this.step=2;this.startTimer(60);this.$nextTick(()=>document.getElementById('otp-1')?.focus());}else{this.error=data.message;}}finally{this.loading=false;}},async verifyOtp(){if(this.otpCode.length<6){this.error='Please enter complete OTP';return;}this.loading=true;this.error=null;try{const resp=await fetch('/auth/otp/verify-login',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]')?.content},body:JSON.stringify({phone:this.phone,otp:this.otpCode})});const data=await resp.json();if(data.success){window.location.href=data.redirect||'/account';}else{this.error=data.message;this.otpCode='';document.querySelectorAll('.otp-box').forEach(i=>i.value='');document.getElementById('otp-1')?.focus();}}finally{this.loading=false;}},startTimer(seconds){this.timer=seconds;clearInterval(this.timerInterval);this.timerInterval=setInterval(()=>{if(this.timer>0){this.timer--;}else{clearInterval(this.timerInterval);}},1000);}}}
</script>
@endsection
