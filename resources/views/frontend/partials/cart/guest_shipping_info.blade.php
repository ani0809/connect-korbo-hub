<div class="p-0">
    <div class="row gutters-10">
        <!-- Name -->
        <div class="col-md-6">
            <div class="form-group">
                <label class="fs-14 fw-700">{{ translate('Full Name')}} <span class="text-danger">*</span></label>
                <input class="form-control mb-3 rounded-0" placeholder="{{ translate('Your Name')}}" name="name" required></input>
            </div>
        </div>

        <!-- Phone -->
        <div class="col-md-6">
            <div class="form-group">
                <label class="fs-14 fw-700">{{ translate('Phone Number')}} <span class="text-danger">*</span></label>
                <input type="tel" id="guest-phone" class="form-control rounded-0" placeholder="01xxxxxxxxx" name="phone" autocomplete="off" required>
                <input type="hidden" name="country_code" value="88">
            </div>
        </div>

        <!-- Address -->
        <div class="col-md-12">
            <div class="form-group">
                <label class="fs-14 fw-700">{{ translate('Full Address')}} <span class="text-danger">*</span></label>
                <textarea class="form-control mb-3 rounded-0" placeholder="{{ translate('House no, Flat no, Road no, Area etc.')}}" rows="2" name="address" required></textarea>
            </div>
        </div>

        <!-- District (City) -->
        <div class="col-md-6">
            <div class="form-group">
                <label class="fs-14 fw-700">{{ translate('District')}} <span class="text-danger">*</span></label>
                <select class="form-control mb-3 cibato-selectpicker rounded-0" data-live-search="true" name="city_id" required id="guest_city_id">
                    <option value="">{{ translate('Select District') }}</option>
                </select>
            </div>
        </div>

        <!-- Area -->
        <div class="col-md-6 area-field d-none">
            <div class="form-group">
                <label class="fs-14 fw-700">{{ translate('Area')}}<span class="text-danger">*</span></label>
                <select class="form-control mb-3 cibato-selectpicker rounded-0 guest-checkout" data-live-search="true" name="area_id">
                    <option value="">{{ translate('Select Area') }}</option>
                </select>
            </div>
        </div>

        <!-- Hidden Technical Fields -->
        <input type="hidden" name="email" value="guest_{{ time() }}@example.com">
        <input type="hidden" name="country_id" value="18">
        <input type="hidden" name="postal_code" value="0000">
        <input type="hidden" name="same_as_shipping" value="1">
    </div>
</div>


