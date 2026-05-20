<x-setting-section title="Cart checkout" description="Configure cart checkout settings.">
    <div class='settings-grid'>
        <x-setting-text label="cart checkout Title" name="cart-checkout__title" :value="\['cart-checkout']['cart-checkout__title'] ?? ''"/>
        <x-setting-toggle label="Enable cart checkout" name="cart-checkout__enabled" :value="\['cart-checkout']['cart-checkout__enabled'] ?? 1"/>
        <x-setting-color label="cart checkout Primary Color" name="cart-checkout__primary_color" :value="\['cart-checkout']['cart-checkout__primary_color'] ?? '#2563eb'"/>
        <x-setting-range label="cart checkout Spacing" name="cart-checkout__spacing" min="0" max="100" :value="\['cart-checkout']['cart-checkout__spacing'] ?? 20"/>
        <x-setting-select label="cart checkout Layout" name="cart-checkout__layout" :value="\['cart-checkout']['cart-checkout__layout'] ?? 'default'" :options="['default'=>'Default','boxed'=>'Boxed','full'=>'Full']"/>
    </div>
    <div class='settings-grid'>
        @for(\ = 1; \ <= 30; \++)
            <x-setting-text :label="'cart checkout Option '.\" :name="'cart-checkout__opt_'.\" :value="\['cart-checkout']['cart-checkout__opt_'.\] ?? ''"/>
        @endfor
    </div>
</x-setting-section>
