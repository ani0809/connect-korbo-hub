@extends('frontend.layouts.app')
@section('title', 'Create Account')
@section('content')
<div class="auth-page"><div class="auth-container"><div class="auth-card">
    <h1 class="auth-title">Create Account</h1><p class="auth-subtitle">Join us and start shopping</p>
    <div class="auth-tabs" x-data="{ tab: 'email' }">
        <div class="auth-tab-buttons"><button @click="tab='email'" :class="{ 'active': tab==='email' }" class="auth-tab-btn">📧 Email</button>@if(feature('otp_login'))<button @click="tab='phone'" :class="{ 'active': tab==='phone' }" class="auth-tab-btn">📱 Phone</button>@endif</div>
        <div x-show="tab==='email'" x-transition>
            <form method="POST" action="{{ route('register.post') }}">
                @csrf
                <div class="form-group"><label class="form-label">Full Name *</label><input type="text" name="name" value="{{ old('name') }}" class="form-input" required></div>
                <div class="form-group"><label class="form-label">Email Address *</label><input type="email" name="email" value="{{ old('email') }}" class="form-input" required></div>
                <div class="form-group"><label class="form-label">Phone Number</label><div class="phone-input-group"><div class="phone-prefix">🇧🇩 +880</div><input type="tel" name="phone" value="{{ old('phone') }}" class="form-input" placeholder="01XXXXXXXXX"></div></div>
                <div class="form-group" x-data="passwordStrength()"><label class="form-label">Password *</label><input type="password" name="password" class="form-input" @input="check($event.target.value)" required><div class="password-strength"><div class="strength-bar"><div class="strength-fill" :style="'width:' + strength + '%;background:' + color"></div></div><span class="strength-label" x-text="label" :style="'color:' + color"></span></div></div>
                <div class="form-group"><label class="form-label">Confirm Password *</label><input type="password" name="password_confirmation" class="form-input" required></div>
                <div class="form-group"><label class="checkbox-label"><input type="checkbox" name="terms" required><span>I agree to Terms and Privacy</span></label></div>
                <button class="btn-auth-submit">Create Account →</button>
            </form>
        </div>
        @if(feature('otp_login'))
        <div x-show="tab==='phone'" x-transition x-data="OtpRegister()">
            <div x-show="step===1">
                <div class="form-group"><label class="form-label">Full Name *</label><input type="text" x-model="form.name" class="form-input"></div>
                <div class="form-group"><label class="form-label">Phone Number *</label><div class="phone-input-group"><div class="phone-prefix">🇧🇩 +880</div><input type="tel" x-model="form.phone" class="form-input" maxlength="11"></div></div>
                <div class="form-group"><label class="form-label">Email (optional)</label><input type="email" x-model="form.email" class="form-input"></div>
                <div class="form-group"><label class="form-label">Password *</label><input type="password" x-model="form.password" class="form-input"></div>
                <div class="alert-error" x-show="error" x-text="error"></div>
                <button @click="sendOtp()" class="btn-auth-submit" :disabled="loading"><span x-show="!loading">Send OTP →</span><span x-show="loading">Sending...</span></button>
            </div>
            <div x-show="step===2">
                <div class="otp-sent-info"><div class="otp-sent-icon">📱</div><div><div class="otp-sent-title">Verify Your Phone</div><div class="otp-sent-sub">OTP sent to +880 <span x-text="form.phone"></span></div></div></div>
                <div class="form-group"><label class="form-label">Enter 6-Digit OTP</label><div class="otp-input-row" x-data="OtpInputs('reg-otp','reg-otp-combined')"><template x-for="i in 6" :key="i"><input type="text" inputmode="numeric" maxlength="1" class="otp-box" :id="'reg-otp-'+i" @input="handleInput($event, i)" @keydown="handleKeydown($event, i)" @paste="handlePaste($event)" x-model="digits[i-1]"></template></div><input type="hidden" x-model="otpCode" id="reg-otp-combined"></div>
                <div class="otp-timer"><span x-show="timer>0">Resend in <span class="timer-count" x-text="timer + 's'"></span></span><button type="button" x-show="timer===0" @click="sendOtp()" class="resend-btn">🔄 Resend OTP</button></div>
                <div class="alert-error" x-show="error" x-text="error"></div>
                <button @click="verifyAndRegister()" class="btn-auth-submit" :disabled="otpCode.length<6 || loading"><span x-show="!loading">✅ Verify & Create Account</span><span x-show="loading">Creating...</span></button>
                <button type="button" @click="step=1;error=null" class="btn-change-phone">← Back</button>
            </div>
        </div>
        @endif
    </div>
    @include('auth.partials.social-login-buttons')
    <p class="auth-switch">Already have an account? <a href="{{ route('login') }}">Sign in →</a></p>
</div></div></div>
<script>
function OtpRegister(){return{step:1,form:{name:'',phone:'',email:'',password:''},otpCode:'',loading:false,error:null,timer:0,timerInterval:null,async sendOtp(){if(!this.form.name){this.error='Name is required';return;}if(this.form.phone.length<11){this.error='Enter valid phone number';return;}if(!this.form.password||this.form.password.length<6){this.error='Password min 6 characters';return;}this.loading=true;this.error=null;try{const resp=await fetch('/auth/otp/send',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]')?.content},body:JSON.stringify({phone:this.form.phone,type:'register'})});const data=await resp.json();if(data.success){this.step=2;this.startTimer(60);this.$nextTick(()=>document.getElementById('reg-otp-1')?.focus());}else{this.error=data.message;if(data.action==='login'){setTimeout(()=>{window.location.href='/login';},1500);}}}finally{this.loading=false;}},async verifyAndRegister(){this.loading=true;this.error=null;try{const resp=await fetch('/auth/otp/verify-register',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]')?.content},body:JSON.stringify({...this.form,otp:this.otpCode,password_confirmation:this.form.password})});const data=await resp.json();if(data.success){window.location.href=data.redirect||'/account';}else{this.error=data.message;this.otpCode='';}}finally{this.loading=false;}},startTimer(seconds){this.timer=seconds;clearInterval(this.timerInterval);this.timerInterval=setInterval(()=>{if(this.timer>0){this.timer--;}else{clearInterval(this.timerInterval);}},1000);}}}
</script>
@endsection
