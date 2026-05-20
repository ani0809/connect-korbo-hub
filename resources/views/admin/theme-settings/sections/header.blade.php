<x-setting-section title="Header" description="Configure header settings.">
    <div class='settings-grid'>
        <x-setting-text label="header Title" name="header__title" :value="\['header']['header__title'] ?? ''"/>
        <x-setting-toggle label="Enable header" name="header__enabled" :value="\['header']['header__enabled'] ?? 1"/>
        <x-setting-color label="header Primary Color" name="header__primary_color" :value="\['header']['header__primary_color'] ?? '#2563eb'"/>
        <x-setting-range label="header Spacing" name="header__spacing" min="0" max="100" :value="\['header']['header__spacing'] ?? 20"/>
        <x-setting-select label="header Layout" name="header__layout" :value="\['header']['header__layout'] ?? 'default'" :options="['default'=>'Default','boxed'=>'Boxed','full'=>'Full']"/>
    </div>
    <div class='settings-grid'>
        @for(\ = 1; \ <= 30; \++)
            <x-setting-text :label="'header Option '.\" :name="'header__opt_'.\" :value="\['header']['header__opt_'.\] ?? ''"/>
        @endfor
    </div>
</x-setting-section>
