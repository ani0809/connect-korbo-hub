@extends('admin.layouts.app')
@section('title', 'Seller settings')
@section('content')
@include('admin.settings._system_nav')
<form method="post" action="{{ route('admin.settings.seller.save') }}" class="bg-white border rounded-xl p-6 space-y-3 max-w-3xl">
  @csrf
  <label class="flex items-center gap-2 text-sm"><input type="hidden" name="seller_registration" value="0"><input type="checkbox" name="seller_registration" value="1" {{ ($settings['seller_registration'] ?? '1') === '1' ? 'checked' : '' }}> Allow seller registration</label>
  <label class="flex items-center gap-2 text-sm"><input type="hidden" name="seller_auto_approve" value="0"><input type="checkbox" name="seller_auto_approve" value="1" {{ ($settings['seller_auto_approve'] ?? '0') === '1' ? 'checked' : '' }}> Auto-approve sellers</label>
  <label class="flex items-center gap-2 text-sm"><input type="hidden" name="seller_auto_approve_products" value="0"><input type="checkbox" name="seller_auto_approve_products" value="1" {{ ($settings['seller_auto_approve_products'] ?? '0') === '1' ? 'checked' : '' }}> Auto-approve seller products</label>
  <label class="flex items-center gap-2 text-sm"><input type="hidden" name="seller_business_required" value="0"><input type="checkbox" name="seller_business_required" value="1" {{ ($settings['seller_business_required'] ?? '1') === '1' ? 'checked' : '' }}> Business info required</label>
  <label class="flex items-center gap-2 text-sm"><input type="hidden" name="seller_kyc" value="0"><input type="checkbox" name="seller_kyc" value="1" {{ ($settings['seller_kyc'] ?? '0') === '1' ? 'checked' : '' }}> Seller verification required</label>
  <div><label class="text-sm">Min withdrawal</label><input type="number" step="0.01" name="seller_min_withdrawal" value="{{ $settings['seller_min_withdrawal'] ?? '10' }}" class="w-full border rounded px-3 py-2 mt-1"></div>
  <label class="flex items-center gap-2 text-sm"><input type="hidden" name="seller_payout_bank" value="0"><input type="checkbox" name="seller_payout_bank" value="1" {{ ($settings['seller_payout_bank'] ?? '1') === '1' ? 'checked' : '' }}> Bank transfer</label>
  <label class="flex items-center gap-2 text-sm"><input type="hidden" name="seller_payout_bkash" value="0"><input type="checkbox" name="seller_payout_bkash" value="1" {{ ($settings['seller_payout_bkash'] ?? '1') === '1' ? 'checked' : '' }}> bKash</label>
  <label class="flex items-center gap-2 text-sm"><input type="hidden" name="seller_payout_paypal" value="0"><input type="checkbox" name="seller_payout_paypal" value="1" {{ ($settings['seller_payout_paypal'] ?? '0') === '1' ? 'checked' : '' }}> PayPal</label>
  <label class="flex items-center gap-2 text-sm"><input type="hidden" name="seller_payout_nagad" value="0"><input type="checkbox" name="seller_payout_nagad" value="1" {{ ($settings['seller_payout_nagad'] ?? '0') === '1' ? 'checked' : '' }}> Nagad</label>
  <label class="flex items-center gap-2 text-sm"><input type="hidden" name="seller_feature_coupons" value="0"><input type="checkbox" name="seller_feature_coupons" value="1" {{ ($settings['seller_feature_coupons'] ?? '0') === '1' ? 'checked' : '' }}> Sellers can create coupons</label>
  <label class="flex items-center gap-2 text-sm"><input type="hidden" name="seller_feature_flash" value="0"><input type="checkbox" name="seller_feature_flash" value="1" {{ ($settings['seller_feature_flash'] ?? '0') === '1' ? 'checked' : '' }}> Sellers can create flash deals</label>
  <label class="flex items-center gap-2 text-sm"><input type="hidden" name="seller_chat" value="0"><input type="checkbox" name="seller_chat" value="1" {{ ($settings['seller_chat'] ?? '0') === '1' ? 'checked' : '' }}> Allow seller chat</label>
  <a href="{{ route('admin.commission.index') }}" class="text-blue-600 text-sm">Commission settings →</a>
  <button type="submit" class="btn-primary block mt-4">Save</button>
</form>
@endsection
