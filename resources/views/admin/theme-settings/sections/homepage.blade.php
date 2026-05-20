<x-setting-section title="Homepage" description="Configure homepage settings.">
    <div class='settings-grid'>
        <x-setting-text label="homepage Title" name="homepage__title" :value="\['homepage']['homepage__title'] ?? ''"/>
        <x-setting-toggle label="Enable homepage" name="homepage__enabled" :value="\['homepage']['homepage__enabled'] ?? 1"/>
        <x-setting-color label="homepage Primary Color" name="homepage__primary_color" :value="\['homepage']['homepage__primary_color'] ?? '#2563eb'"/>
        <x-setting-range label="homepage Spacing" name="homepage__spacing" min="0" max="100" :value="\['homepage']['homepage__spacing'] ?? 20"/>
        <x-setting-select label="homepage Layout" name="homepage__layout" :value="\['homepage']['homepage__layout'] ?? 'default'" :options="['default'=>'Default','boxed'=>'Boxed','full'=>'Full']"/>
    </div>
    <div class='settings-grid'>
        @for(\ = 1; \ <= 30; \++)
            <x-setting-text :label="'homepage Option '.\" :name="'homepage__opt_'.\" :value="\['homepage']['homepage__opt_'.\] ?? ''"/>
        @endfor
    </div>
</x-setting-section>
