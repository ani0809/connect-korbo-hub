<x-setting-section title="Page 404" description="Configure page 404 settings.">
    <div class='settings-grid'>
        <x-setting-text label="page 404 Title" name="page-404__title" :value="\['page-404']['page-404__title'] ?? ''"/>
        <x-setting-toggle label="Enable page 404" name="page-404__enabled" :value="\['page-404']['page-404__enabled'] ?? 1"/>
        <x-setting-color label="page 404 Primary Color" name="page-404__primary_color" :value="\['page-404']['page-404__primary_color'] ?? '#2563eb'"/>
        <x-setting-range label="page 404 Spacing" name="page-404__spacing" min="0" max="100" :value="\['page-404']['page-404__spacing'] ?? 20"/>
        <x-setting-select label="page 404 Layout" name="page-404__layout" :value="\['page-404']['page-404__layout'] ?? 'default'" :options="['default'=>'Default','boxed'=>'Boxed','full'=>'Full']"/>
    </div>
    <div class='settings-grid'>
        @for(\ = 1; \ <= 30; \++)
            <x-setting-text :label="'page 404 Option '.\" :name="'page-404__opt_'.\" :value="\['page-404']['page-404__opt_'.\] ?? ''"/>
        @endfor
    </div>
</x-setting-section>
