<x-setting-section title="Custom code" description="Configure custom code settings.">
    <div class='settings-grid'>
        <x-setting-text label="custom code Title" name="custom-code__title" :value="\['custom-code']['custom-code__title'] ?? ''"/>
        <x-setting-toggle label="Enable custom code" name="custom-code__enabled" :value="\['custom-code']['custom-code__enabled'] ?? 1"/>
        <x-setting-color label="custom code Primary Color" name="custom-code__primary_color" :value="\['custom-code']['custom-code__primary_color'] ?? '#2563eb'"/>
        <x-setting-range label="custom code Spacing" name="custom-code__spacing" min="0" max="100" :value="\['custom-code']['custom-code__spacing'] ?? 20"/>
        <x-setting-select label="custom code Layout" name="custom-code__layout" :value="\['custom-code']['custom-code__layout'] ?? 'default'" :options="['default'=>'Default','boxed'=>'Boxed','full'=>'Full']"/>
    </div>
    <div class='settings-grid'>
        @for(\ = 1; \ <= 30; \++)
            <x-setting-text :label="'custom code Option '.\" :name="'custom-code__opt_'.\" :value="\['custom-code']['custom-code__opt_'.\] ?? ''"/>
        @endfor
    </div>
</x-setting-section>
