@extends('frontend.layouts.app')
@section('title', 'Reset Password')
@section('content')
<div class="auth-page">
    <div class="auth-container">
        <div class="auth-card">
            <h1 class="auth-title">Reset Password</h1>
            <p class="auth-subtitle">Reset via Email or Phone OTP</p>

            <div class="auth-tabs" x-data="{ tab: 'email' }">
                <div class="auth-tab-buttons">
                    <button @click="tab='email'" :class="{ 'active': tab==='email' }" class="auth-tab-btn">📧 Email</button>
                    @if(feature('otp_login'))
                        <button @click="tab='phone'" :class="{ 'active': tab==='phone' }" class="auth-tab-btn">📱 Phone OTP</button>
                    @endif
                </div>

                <div x-show="tab==='email'" x-transition>
                    @if(session('status'))
                        <div class="alert-success">{{ session('status') }}</div>
                    @endif
                    <form method="POST" action="{{ route('password.email') }}" class="space-y-3">
                        @csrf
                        <div class="form-group">
                            <label class="form-label">Email Address</label>
                            <input type="email" name="email" class="form-input" required>
                        </div>
                        <button type="submit" class="btn-auth-submit">Send Reset Link →</button>
                    </form>
                </div>

                @if(feature('otp_login'))
                    <div x-show="tab==='phone'" x-transition x-data="PhoneReset()">
                        <div x-show="step===1" class="space-y-3">
                            <div class="form-group">
                                <label class="form-label">Phone Number</label>
                                <div class="phone-input-group">
                                    <div class="phone-prefix">🇧🇩 +880</div>
                                    <input type="tel" x-model="phone" class="form-input" maxlength="11">
                                </div>
                            </div>
                            <div class="alert-error" x-show="error" x-text="error"></div>
                            <button @click="sendOtp()" class="btn-auth-submit" :disabled="loading">
                                <span x-show="!loading">Send OTP →</span><span x-show="loading">Sending...</span>
                            </button>
                        </div>

                        <div x-show="step===2" class="space-y-3">
                            <div class="otp-sent-info">
                                <div class="otp-sent-icon">📱</div>
                                <div>
                                    <div class="otp-sent-title">OTP Sent</div>
                                    <div class="otp-sent-sub">Enter the code and set your new password.</div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Enter OTP</label>
                                <div class="otp-input-row" x-data="OtpInputs('fp-otp','fp-otp-combined')">
                                    <template x-for="i in 6" :key="i"><input type="text" inputmode="numeric" maxlength="1" class="otp-box" :id="'fp-otp-'+i" @input="handleInput($event,i)" @keydown="handleKeydown($event,i)" @paste="handlePaste($event)" x-model="digits[i-1]"></template>
                                </div>
                                <input type="hidden" x-model="otpCode" id="fp-otp-combined">
                            </div>
                            <div class="form-group">
                                <label class="form-label">New Password *</label>
                                <input type="password" x-model="newPassword" class="form-input">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Confirm New Password *</label>
                                <input type="password" x-model="confirmPassword" class="form-input">
                            </div>
                            <div class="otp-timer"><span x-show="timer>0">Resend in <span class="timer-count" x-text="timer + 's'"></span></span><button type="button" x-show="timer===0" @click="sendOtp()" class="resend-btn">🔄 Resend OTP</button></div>
                            <div class="alert-error" x-show="error" x-text="error"></div>
                            <div class="alert-success" x-show="success" x-text="success"></div>
                            <button @click="resetPassword()" class="btn-auth-submit" :disabled="loading || otpCode.length<6 || !newPassword"><span x-show="!loading">🔒 Reset Password</span><span x-show="loading">Resetting...</span></button>
                        </div>
                    </div>
                @endif
            </div>

            <p class="auth-switch">Remember your password? <a href="{{ route('login') }}">Sign in →</a></p>
        </div>
    </div>
</div>
<script>
function PhoneReset(){return{step:1,phone:'',otpCode:'',newPassword:'',confirmPassword:'',loading:false,error:null,success:null,timer:0,timerInterval:null,async sendOtp(){this.loading=true;this.error=null;try{const resp=await fetch('/auth/otp/send',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]')?.content},body:JSON.stringify({phone:this.phone,type:'forgot'})});const data=await resp.json();if(data.success){this.step=2;this.startTimer(60);this.$nextTick(()=>document.getElementById('fp-otp-1')?.focus());}else{this.error=data.message;}}finally{this.loading=false;}},async resetPassword(){if(this.newPassword!==this.confirmPassword){this.error='Passwords do not match';return;}this.loading=true;this.error=null;try{const resp=await fetch('/auth/otp/reset-password',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]')?.content},body:JSON.stringify({phone:this.phone,otp:this.otpCode,password:this.newPassword,password_confirmation:this.confirmPassword})});const data=await resp.json();if(data.success){this.success='✅ Password reset! Redirecting...';setTimeout(()=>window.location.href='/login',1500);}else{this.error=data.message;}}finally{this.loading=false;}},startTimer(seconds){this.timer=seconds;clearInterval(this.timerInterval);this.timerInterval=setInterval(()=>{this.timer>0?this.timer--:clearInterval(this.timerInterval);},1000);}}}
</script>
@endsection
