@php
    $admin_carts = array();
    $seller_carts = array();
    foreach ($carts as $key => $cartItem){
        $product = get_single_product($cartItem['product_id']);

        if($product->added_by == 'admin'){
            $admin_carts[] = $cartItem;
        }
        else{
            $seller_carts[$product->user_id][] = $cartItem;
        }
    }

    $pickup_point_list = array();
    if (get_setting('pickup_point') == 1) {
        $pickup_point_list = get_all_pickup_points();
    }
@endphp

<!-- Store products (cart group) -->
@if (!empty($admin_carts))
    @include('frontend.partials.cart.delivery_info_details', ['owner_carts' => $admin_carts, 'owner_id' => get_admin()->id ])
@endif
<input type="hidden" id="carrierCount" value="{{ count($carrier_list) }}">
<!-- Seller Products -->
@if (!empty($seller_carts))
    @foreach ($seller_carts as $key => $owner_carts)
        @include('frontend.partials.cart.delivery_info_details', ['owner_carts' => $owner_carts, 'owner_id' => $key ])
    @endforeach
@endif

