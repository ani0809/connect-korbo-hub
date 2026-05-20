<x-setting-section title="Shop" description="Configure shop settings.">
    <div class='settings-grid'>
        <x-setting-text label="shop Title" name="shop__title" :value="\['shop']['shop__title'] ?? ''"/>
        <x-setting-toggle label="Enable shop" name="shop__enabled" :value="\['shop']['shop__enabled'] ?? 1"/>
        <x-setting-color label="shop Primary Color" name="shop__primary_color" :value="\['shop']['shop__primary_color'] ?? '#2563eb'"/>
        <x-setting-range label="shop Spacing" name="shop__spacing" min="0" max="100" :value="\['shop']['shop__spacing'] ?? 20"/>
        <x-setting-select label="shop Layout" name="shop__layout" :value="\['shop']['shop__layout'] ?? 'default'" :options="['default'=>'Default','boxed'=>'Boxed','full'=>'Full']"/>
    </div>
    <div class='settings-grid'>
        @for(\ = 1; \ <= 30; \++)
            <x-setting-text :label="'shop Option '.\" :name="'shop__opt_'.\" :value="\['shop']['shop__opt_'.\] ?? ''"/>
        @endfor
    </div>
</x-setting-section>
