<x-setting-section title="Preloader" description="Configure preloader settings.">
    <div class='settings-grid'>
        <x-setting-text label="preloader Title" name="preloader__title" :value="\['preloader']['preloader__title'] ?? ''"/>
        <x-setting-toggle label="Enable preloader" name="preloader__enabled" :value="\['preloader']['preloader__enabled'] ?? 1"/>
        <x-setting-color label="preloader Primary Color" name="preloader__primary_color" :value="\['preloader']['preloader__primary_color'] ?? '#2563eb'"/>
        <x-setting-range label="preloader Spacing" name="preloader__spacing" min="0" max="100" :value="\['preloader']['preloader__spacing'] ?? 20"/>
        <x-setting-select label="preloader Layout" name="preloader__layout" :value="\['preloader']['preloader__layout'] ?? 'default'" :options="['default'=>'Default','boxed'=>'Boxed','full'=>'Full']"/>
    </div>
    <div class='settings-grid'>
        @for(\ = 1; \ <= 30; \++)
            <x-setting-text :label="'preloader Option '.\" :name="'preloader__opt_'.\" :value="\['preloader']['preloader__opt_'.\] ?? ''"/>
        @endfor
    </div>
</x-setting-section>
