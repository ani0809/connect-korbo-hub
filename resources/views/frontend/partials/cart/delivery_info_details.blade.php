<div class="row">
    @php
        $physical = false;
        foreach ($owner_carts as $cartItem){
            $product = get_single_product($cartItem['product_id']);
            if ($product->digital == 0) {
                $physical = true;
                break;
            }
        }
    @endphp
    <!-- Product List -->
    <div class="col-12">
        <ul class="list-group list-group-flush mb-2">
            @foreach ($owner_carts as $cartItem)
                @php
                    $product = get_single_product($cartItem['product_id']);
                    $product_stock = $product->stocks->where('variant', $cartItem->variation)->first();
                @endphp
                <li class="list-group-item px-0 py-2 border-bottom border-dashed">
                    <div class="row gutters-5 align-items-center">
                        <!-- Image -->
                        <div class="col-auto">
                            <img src="{{ get_image($product->thumbnail) }}"
                                class="img-fit size-60px"
                                alt="{{ $product->getTranslation('name') }}"
                                onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';">
                        </div>
                        <!-- Info -->
                        <div class="col">
                            <div class="fs-13 fw-400 text-dark text-truncate-2 mb-1">{{ $product->getTranslation('name') }}</div>
                            <div class="text-secondary fs-11">
                                @if ($cartItem->variation != '')
                                    <span>{{ translate('Variation') }}: {{ $cartItem->variation }}</span> |
                                @endif
                                <span>{{ single_price(cart_product_price($cartItem, $product, false)) }}</span>
                            </div>
                            
                            <!-- Qty & Total -->
                            <div class="d-flex align-items-center justify-content-between mt-2">
                                <div class="cibato-plus-minus d-flex align-items-center bg-light" style="width: 80px;">
                                    <button class="btn btn-sm btn-icon border-0" type="button" data-type="plus" data-field="quantity[{{ $cartItem->id }}]">
                                        <i class="las la-plus"></i>
                                    </button>
                                    <input type="number" name="quantity[{{ $cartItem->id }}]" 
                                        class="form-control h-auto border-0 bg-transparent text-center px-0 fs-13 input-number" 
                                        value="{{ $cartItem->quantity }}" min="{{ $product->min_qty }}" max="{{ $product_stock->qty }}" 
                                        onchange="updateQuantity({{ $cartItem->id }}, this)">
                                    <button class="btn btn-sm btn-icon border-0" type="button" data-type="minus" data-field="quantity[{{ $cartItem->id }}]">
                                        <i class="las la-minus"></i>
                                    </button>
                                </div>
                                <div class="fw-700 text-primary fs-14">
                                    {{ single_price(cart_product_price($cartItem, $product, false) * $cartItem->quantity) }}
                                </div>
                            </div>
                        </div>
                        <!-- Remove -->
                        <div class="col-auto">
                            <a href="javascript:void(0)" onclick="removeFromCartView(event, {{ $cartItem->id }})" class="text-secondary hov-text-danger p-2">
                                <i class="las la-trash-alt fs-18"></i>
                            </a>
                        </div>
                    </div>
                </li>
            @endforeach
        </ul>
    </div>

    @if ($physical)
    <!-- Choose Delivery Type -->
    <div class="col-12 mb-2">
        <h6 class="fs-14 fw-700 mt-2 mb-3">{{ translate('Choose Delivery Type') }}</h6>
        <div class="row gutters-10">
            <!-- Home Delivery -->
            @if (get_setting('shipping_type') != 'carrier_wise_shipping')
            <div class="col-12 mb-2">
                <label class="cibato-megabox d-block bg-white mb-0">
                    <input type="radio" name="shipping_type_{{ $owner_id }}" value="home_delivery" onchange="show_pickup_point(this, {{ $owner_id }})" checked required>
                    <span class="d-flex cibato-megabox-elem rounded-0 p-2 align-items-center">
                        <span class="cibato-rounded-check flex-shrink-0"></span>
                        <span class="flex-grow-1 pl-3 fw-600 fs-13">{{ translate('Home Delivery') }}</span>
                    </span>
                </label>
            </div>
            <!-- Carrier -->
            @else
            <div class="col-12 mb-2">
                <label class="cibato-megabox d-block bg-white mb-0">
                    <input type="radio" name="shipping_type_{{ $owner_id }}" value="carrier" onchange="show_pickup_point(this, {{ $owner_id }})" checked required>
                    <span class="d-flex cibato-megabox-elem rounded-0 p-2 align-items-center">
                        <span class="cibato-rounded-check flex-shrink-0"></span>
                        <span class="flex-grow-1 pl-3 fw-600 fs-13">{{ translate('Carrier') }}</span>
                    </span>
                </label>
            </div>
            @endif
            <!-- Local Pickup -->
            @if ($pickup_point_list)
            <div class="col-12 mb-2">
                <label class="cibato-megabox d-block bg-white mb-0">
                    <input type="radio" name="shipping_type_{{ $owner_id }}" value="pickup_point" onchange="show_pickup_point(this, {{ $owner_id }})" required>
                    <span class="d-flex cibato-megabox-elem rounded-0 p-2 align-items-center">
                        <span class="cibato-rounded-check flex-shrink-0"></span>
                        <span class="flex-grow-1 pl-3 fw-600 fs-13">{{ translate('Local Pickup') }}</span>
                    </span>
                </label>
            </div>
            @endif
        </div>

        <!-- Pickup Point List & Carrier List Logic remains similar but adjusted for col-12 ... -->
        <!-- Pickup Point List -->
        @if ($pickup_point_list)
        <div class="mt-2 pickup_point_id_{{ $owner_id }} d-none">
            <select class="form-control cibato-selectpicker rounded-0" name="pickup_point_id_{{ $owner_id }}" data-live-search="true" onchange="updateDeliveryInfo('pickup_point', this.value, {{ $owner_id }})">
                <option value="">{{ translate('Select your nearest pickup point')}}</option>
                @foreach ($pickup_point_list as $pick_up_point)
                <option value="{{ $pick_up_point->id }}" data-content="<span class='d-block'><span class='d-block fs-14 fw-600'>{{ $pick_up_point->getTranslation('name') }}</span><span class='d-block opacity-50 fs-11'>{{ $pick_up_point->getTranslation('address') }}</span></span>"></option>
                @endforeach
            </select>
        </div>
        @endif

        <!-- Carrier Wise Shipping -->
        @if (get_setting('shipping_type') == 'carrier_wise_shipping')
        <div class="row pt-2 carrier_id_{{ $owner_id }}">
            @if($carrier_list->isEmpty())
                <div class="col-12"><div class="alert alert-danger fs-12 mb-0">{{ translate('Shipping is not available.') }}</div></div>
            @else
                @foreach($carrier_list as $carrier_key => $carrier)
                <div class="col-12 mb-2">
                    <label class="cibato-megabox d-block bg-white mb-0">
                        <input type="radio" name="carrier_id_{{ $owner_id }}" value="{{ $carrier->id }}" @if($carrier_key==0) checked @endif onchange="updateDeliveryInfo('carrier', {{ $carrier->id }}, {{ $owner_id }})">
                        <span class="d-flex p-2 cibato-megabox-elem rounded-0 align-items-center">
                            <span class="cibato-rounded-check flex-shrink-0"></span>
                            <span class="flex-grow-1 pl-2">
                                <img src="{{ uploaded_asset($carrier->logo)}}" alt="Image" class="w-40px img-fit">
                            </span>
                            <span class="flex-grow-1 pl-2 fw-700 fs-11 text-truncate">{{ $carrier->name }}</span>
                            <span class="flex-grow-1 pl-2 fw-600 fs-11 text-right">{{ single_price(carrier_base_price($carts, $carrier->id, $owner_id, $shipping_info)) }}</span>
                        </span>
                    </label>
                </div>
                @endforeach
            @endif
        </div>
        @endif
    </div>
    @endif
</div>
