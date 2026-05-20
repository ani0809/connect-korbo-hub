@extends('frontend.layouts.app')
@section('title', 'Track Order')

@section('content')
<div class="tracking-page">
    <div class="container">
        <div class="tracking-hero">
            <h1 class="tracking-title">📦 Track Your Order</h1>
            <p class="tracking-subtitle">Enter your order number or tracking code below</p>

            <div class="tracking-form" x-data="OrderTracker()">
                <div class="tracking-input-group">
                    <input type="text" x-model="query" placeholder="Order number (e.g. #ORD-2024-001) or tracking code" class="tracking-input" @keyup.enter="track()">
                    <button @click="track()" class="tracking-btn" :disabled="loading">
                        <span x-show="!loading">🔍 Track</span>
                        <span x-show="loading">⏳ Tracking...</span>
                    </button>
                </div>

                <div x-show="error" class="tracking-error" x-text="error"></div>

                <div x-show="result" x-transition class="tracking-result">
                    <div class="track-order-header">
                        <div class="track-order-num">Order # <span x-text="result?.order_number"></span></div>
                        <div class="track-order-date">Placed: <span x-text="result?.order_date"></span></div>
                    </div>

                    <div class="track-status-banner" :class="'status-' + (result?.status ?? '')">
                        <div class="status-icon" x-text="getStatusIcon(result?.status)"></div>
                        <div class="status-info">
                            <div class="status-title" x-text="getStatusLabel(result?.status)"></div>
                            <div class="status-message" x-text="result?.status_message"></div>
                        </div>
                    </div>

                    <div class="tracking-progress">
                        <template x-for="(step, i) in trackingSteps" :key="i">
                            <div class="track-step" :class="{ completed: step.completed, active: step.active }">
                                <div class="track-step-icon" x-text="step.icon"></div>
                                <div class="track-step-line" x-show="i < trackingSteps.length - 1" :class="{ filled: step.completed }"></div>
                                <div class="track-step-label" x-text="step.label"></div>
                            </div>
                        </template>
                    </div>

                    <div class="track-courier-info" x-show="result?.tracking_code">
                        <div class="courier-row"><span class="courier-label">Courier:</span><span class="courier-name" x-text="result?.courier_name"></span></div>
                        <div class="courier-row"><span class="courier-label">Tracking Code:</span><span class="courier-code" x-text="result?.tracking_code"></span><button @click="copyTracking(result?.tracking_code)" class="btn-copy-code">📋 Copy</button></div>
                    </div>

                    <div class="tracking-timeline" x-show="result?.events?.length > 0">
                        <h4 class="timeline-title">Tracking History</h4>
                        <div class="timeline-events">
                            <template x-for="(event, i) in result?.events" :key="i">
                                <div class="timeline-event" :class="{ latest: i === 0 }">
                                    <div class="event-dot"></div>
                                    <div class="event-content">
                                        <div class="event-status" x-text="event.status"></div>
                                        <div class="event-message" x-text="event.message"></div>
                                        <div class="event-location" x-show="event.location" x-text="'📍 ' + event.location"></div>
                                        <div class="event-time" x-text="event.time"></div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <div class="track-order-items" x-show="result?.items?.length > 0">
                        <h4 class="items-title">Order Items</h4>
                        <template x-for="item in result?.items" :key="item.id">
                            <div class="track-item">
                                <img :src="item.image" :alt="item.name" class="track-item-img">
                                <div class="track-item-info">
                                    <div class="track-item-name" x-text="item.name"></div>
                                    <div class="track-item-qty" x-text="'x' + item.qty"></div>
                                </div>
                                <div class="track-item-price" x-text="item.price"></div>
                            </div>
                        </template>
                    </div>

                    <div class="track-address" x-show="result?.address">
                        <h4>Delivery Address</h4>
                        <p x-text="result?.address"></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function OrderTracker() {
    return {
        query: @json($track ?? request('track') ?? ''),
        loading: false,
        error: null,
        result: null,
        trackingSteps: [],
        async init() {
            if (this.query) await this.track();
        },
        async track() {
            if (!this.query.trim()) {
                this.error = 'Please enter order number or tracking code';
                return;
            }
            this.loading = true;
            this.error = null;
            this.result = null;
            try {
                const resp = await fetch('/orders/track?q=' + encodeURIComponent(this.query.trim()));
                const data = await resp.json();
                if (data.success) {
                    this.result = data;
                    this.buildTrackingSteps(data.status);
                } else {
                    this.error = data.message || 'Order not found';
                }
            } catch (e) {
                this.error = 'Network error. Try again.';
            } finally {
                this.loading = false;
            }
        },
        buildTrackingSteps(currentStatus) {
            this.trackingSteps = [
                { label: 'Order Placed', icon: '📋', completed: true, active: currentStatus === 'pending' },
                { label: 'Confirmed', icon: '✅', completed: ['confirmed','processing','shipped','in_transit','out_for_delivery','delivered'].includes(currentStatus), active: currentStatus === 'confirmed' },
                { label: 'Processing', icon: '📦', completed: ['processing','shipped','in_transit','out_for_delivery','delivered'].includes(currentStatus), active: currentStatus === 'processing' },
                { label: 'Shipped', icon: '🚚', completed: ['shipped','in_transit','out_for_delivery','delivered'].includes(currentStatus), active: ['shipped','in_transit','out_for_delivery'].includes(currentStatus) },
                { label: 'Delivered', icon: '🎉', completed: currentStatus === 'delivered', active: currentStatus === 'delivered' },
            ];
        },
        getStatusIcon(status) {
            return ({ pending:'⏳', confirmed:'✅', processing:'📦', shipped:'🚚', in_transit:'🚚', out_for_delivery:'🏃', delivered:'🎉', cancelled:'❌', returned:'↩️' })[status] || '📋';
        },
        getStatusLabel(status) {
            return ({ pending:'Order Pending', confirmed:'Order Confirmed', processing:'Being Processed', shipped:'Shipped', in_transit:'In Transit', out_for_delivery:'Out for Delivery', delivered:'Delivered! 🎉', cancelled:'Cancelled', returned:'Returned' })[status] || `Order Status: ${status}`;
        },
        copyTracking(code) {
            navigator.clipboard.writeText(code || '');
            window.toast?.success('Tracking code copied!');
        }
    }
}
</script>
@endsection
