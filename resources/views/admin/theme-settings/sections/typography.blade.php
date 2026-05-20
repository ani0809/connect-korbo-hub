<x-setting-section title="Typography" description="Configure typography settings.">
    <div class='settings-grid'>
        <x-setting-text label="typography Title" name="typography__title" :value="\['typography']['typography__title'] ?? ''"/>
        <x-setting-toggle label="Enable typography" name="typography__enabled" :value="\['typography']['typography__enabled'] ?? 1"/>
        <x-setting-color label="typography Primary Color" name="typography__primary_color" :value="\['typography']['typography__primary_color'] ?? '#2563eb'"/>
        <x-setting-range label="typography Spacing" name="typography__spacing" min="0" max="100" :value="\['typography']['typography__spacing'] ?? 20"/>
        <x-setting-select label="typography Layout" name="typography__layout" :value="\['typography']['typography__layout'] ?? 'default'" :options="['default'=>'Default','boxed'=>'Boxed','full'=>'Full']"/>
    </div>
    <div class='settings-grid'>
        @for(\ = 1; \ <= 30; \++)
            <x-setting-text :label="'typography Option '.\" :name="'typography__opt_'.\" :value="\['typography']['typography__opt_'.\] ?? ''"/>
        @endfor
    </div>
</x-setting-section>
