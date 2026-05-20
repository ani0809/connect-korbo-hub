<x-setting-section title="Performance" description="Configure performance settings.">
    <div class='settings-grid'>
        <x-setting-text label="performance Title" name="performance__title" :value="\['performance']['performance__title'] ?? ''"/>
        <x-setting-toggle label="Enable performance" name="performance__enabled" :value="\['performance']['performance__enabled'] ?? 1"/>
        <x-setting-color label="performance Primary Color" name="performance__primary_color" :value="\['performance']['performance__primary_color'] ?? '#2563eb'"/>
        <x-setting-range label="performance Spacing" name="performance__spacing" min="0" max="100" :value="\['performance']['performance__spacing'] ?? 20"/>
        <x-setting-select label="performance Layout" name="performance__layout" :value="\['performance']['performance__layout'] ?? 'default'" :options="['default'=>'Default','boxed'=>'Boxed','full'=>'Full']"/>
    </div>
    <div class='settings-grid'>
        @for(\ = 1; \ <= 30; \++)
            <x-setting-text :label="'performance Option '.\" :name="'performance__opt_'.\" :value="\['performance']['performance__opt_'.\] ?? ''"/>
        @endfor
    </div>
</x-setting-section>
