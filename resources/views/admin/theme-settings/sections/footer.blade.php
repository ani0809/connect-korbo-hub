<x-setting-section title="Footer" description="Configure footer settings.">
    <div class='settings-grid'>
        <x-setting-text label="footer Title" name="footer__title" :value="\['footer']['footer__title'] ?? ''"/>
        <x-setting-toggle label="Enable footer" name="footer__enabled" :value="\['footer']['footer__enabled'] ?? 1"/>
        <x-setting-color label="footer Primary Color" name="footer__primary_color" :value="\['footer']['footer__primary_color'] ?? '#2563eb'"/>
        <x-setting-range label="footer Spacing" name="footer__spacing" min="0" max="100" :value="\['footer']['footer__spacing'] ?? 20"/>
        <x-setting-select label="footer Layout" name="footer__layout" :value="\['footer']['footer__layout'] ?? 'default'" :options="['default'=>'Default','boxed'=>'Boxed','full'=>'Full']"/>
    </div>
    <div class='settings-grid'>
        @for(\ = 1; \ <= 30; \++)
            <x-setting-text :label="'footer Option '.\" :name="'footer__opt_'.\" :value="\['footer']['footer__opt_'.\] ?? ''"/>
        @endfor
    </div>
</x-setting-section>
