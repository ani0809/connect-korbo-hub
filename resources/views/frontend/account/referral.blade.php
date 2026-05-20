@extends('frontend.account.layouts.app')
@section('title', 'Refer & Earn')
@section('account-content')
<div class="referral-page">
  <div class="referral-hero-card">
    <div class="ref-hero-emoji">🎁</div>
    <h2 class="ref-hero-title">Refer Friends & Earn!</h2>
    <p class="ref-hero-desc">Share your referral link. When your friend places their first order, both of you earn reward points.</p>
    <div class="ref-rewards-row">
      <div><div class="ref-reward-val">{{ number_format(setting('referral_referred_bonus_points', 100)) }}</div><div class="ref-reward-lbl">pts for friend</div></div>
      <div class="ref-reward-plus">+</div>
      <div><div class="ref-reward-val">{{ number_format(setting('referral_referrer_bonus_points', 200)) }}</div><div class="ref-reward-lbl">pts for you</div></div>
    </div>
  </div>

  <div class="referral-link-card" x-data="{ copied: false }">
    <h3 class="ref-link-title">Your Referral Link</h3>
    <div class="ref-link-input-group">
      <input id="referral-url" class="ref-link-input" value="{{ $stats['referral_url'] }}" readonly>
      <button type="button" class="ref-copy-btn" :class="{ 'copied': copied }" @click="navigator.clipboard.writeText('{{ $stats['referral_url'] }}'); copied=true; setTimeout(()=>copied=false,1200)">
        <span x-show="!copied">Copy</span><span x-show="copied">Copied!</span>
      </button>
    </div>
    <div class="ref-share-buttons">
      <a class="ref-share-btn whatsapp" href="https://wa.me/?text={{ urlencode('Shop with my referral link: '.$stats['referral_url']) }}" target="_blank">WhatsApp</a>
      <a class="ref-share-btn facebook" href="https://facebook.com/sharer/sharer.php?u={{ urlencode($stats['referral_url']) }}" target="_blank">Facebook</a>
      <a class="ref-share-btn twitter" href="https://twitter.com/intent/tweet?text={{ urlencode('Use my referral link: '.$stats['referral_url']) }}" target="_blank">X</a>
    </div>
    <div class="ref-code-section">Or share code: <span class="ref-code-badge">{{ $stats['referral_code'] }}</span></div>
  </div>

  <div class="ref-stats-grid">
    <div class="ref-stat-card"><div class="ref-stat-val">{{ $stats['total_referrals'] }}</div><div class="ref-stat-lbl">Total Referrals</div></div>
    <div class="ref-stat-card"><div class="ref-stat-val success">{{ $stats['completed'] }}</div><div class="ref-stat-lbl">Completed</div></div>
    <div class="ref-stat-card"><div class="ref-stat-val warning">{{ $stats['pending'] }}</div><div class="ref-stat-lbl">Pending</div></div>
    <div class="ref-stat-card"><div class="ref-stat-val primary">{{ number_format($stats['total_points_earned']) }}</div><div class="ref-stat-lbl">Points Earned</div></div>
  </div>
</div>
@endsection
