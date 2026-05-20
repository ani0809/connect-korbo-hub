<x-setting-section title="General" description="Configure general settings.">
    <div class='settings-grid'>
        <x-setting-text label="general Title" name="general__title" :value="\['general']['general__title'] ?? ''"/>
        <x-setting-toggle label="Enable general" name="general__enabled" :value="\['general']['general__enabled'] ?? 1"/>
        <x-setting-color label="general Primary Color" name="general__primary_color" :value="\['general']['general__primary_color'] ?? '#2563eb'"/>
        <x-setting-range label="general Spacing" name="general__spacing" min="0" max="100" :value="\['general']['general__spacing'] ?? 20"/>
        <x-setting-select label="general Layout" name="general__layout" :value="\['general']['general__layout'] ?? 'default'" :options="['default'=>'Default','boxed'=>'Boxed','full'=>'Full']"/>
    </div>
    <div class='settings-grid'>
        @for(\ = 1; \ <= 30; \++)
            <x-setting-text :label="'general Option '.\" :name="'general__opt_'.\" :value="\['general']['general__opt_'.\] ?? ''"/>
        @endfor
    </div>
</x-setting-section>
