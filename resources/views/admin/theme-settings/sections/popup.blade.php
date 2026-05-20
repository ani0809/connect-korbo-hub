<x-setting-section title="Popup" description="Configure popup settings.">
    <div class='settings-grid'>
        <x-setting-text label="popup Title" name="popup__title" :value="\['popup']['popup__title'] ?? ''"/>
        <x-setting-toggle label="Enable popup" name="popup__enabled" :value="\['popup']['popup__enabled'] ?? 1"/>
        <x-setting-color label="popup Primary Color" name="popup__primary_color" :value="\['popup']['popup__primary_color'] ?? '#2563eb'"/>
        <x-setting-range label="popup Spacing" name="popup__spacing" min="0" max="100" :value="\['popup']['popup__spacing'] ?? 20"/>
        <x-setting-select label="popup Layout" name="popup__layout" :value="\['popup']['popup__layout'] ?? 'default'" :options="['default'=>'Default','boxed'=>'Boxed','full'=>'Full']"/>
    </div>
    <div class='settings-grid'>
        @for(\ = 1; \ <= 30; \++)
            <x-setting-text :label="'popup Option '.\" :name="'popup__opt_'.\" :value="\['popup']['popup__opt_'.\] ?? ''"/>
        @endfor
    </div>
</x-setting-section>
