<x-setting-section title="Single product" description="Configure single product settings.">
    <div class='settings-grid'>
        <x-setting-text label="single product Title" name="single-product__title" :value="\['single-product']['single-product__title'] ?? ''"/>
        <x-setting-toggle label="Enable single product" name="single-product__enabled" :value="\['single-product']['single-product__enabled'] ?? 1"/>
        <x-setting-color label="single product Primary Color" name="single-product__primary_color" :value="\['single-product']['single-product__primary_color'] ?? '#2563eb'"/>
        <x-setting-range label="single product Spacing" name="single-product__spacing" min="0" max="100" :value="\['single-product']['single-product__spacing'] ?? 20"/>
        <x-setting-select label="single product Layout" name="single-product__layout" :value="\['single-product']['single-product__layout'] ?? 'default'" :options="['default'=>'Default','boxed'=>'Boxed','full'=>'Full']"/>
    </div>
    <div class='settings-grid'>
        @for(\ = 1; \ <= 30; \++)
            <x-setting-text :label="'single product Option '.\" :name="'single-product__opt_'.\" :value="\['single-product']['single-product__opt_'.\] ?? ''"/>
        @endfor
    </div>
</x-setting-section>
