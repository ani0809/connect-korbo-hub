<x-setting-section title="Mobile" description="Configure mobile settings.">
    <div class='settings-grid'>
        <x-setting-text label="mobile Title" name="mobile__title" :value="\['mobile']['mobile__title'] ?? ''"/>
        <x-setting-toggle label="Enable mobile" name="mobile__enabled" :value="\['mobile']['mobile__enabled'] ?? 1"/>
        <x-setting-color label="mobile Primary Color" name="mobile__primary_color" :value="\['mobile']['mobile__primary_color'] ?? '#2563eb'"/>
        <x-setting-range label="mobile Spacing" name="mobile__spacing" min="0" max="100" :value="\['mobile']['mobile__spacing'] ?? 20"/>
        <x-setting-select label="mobile Layout" name="mobile__layout" :value="\['mobile']['mobile__layout'] ?? 'default'" :options="['default'=>'Default','boxed'=>'Boxed','full'=>'Full']"/>
    </div>
    <div class='settings-grid'>
        @for(\ = 1; \ <= 30; \++)
            <x-setting-text :label="'mobile Option '.\" :name="'mobile__opt_'.\" :value="\['mobile']['mobile__opt_'.\] ?? ''"/>
        @endfor
    </div>
</x-setting-section>
