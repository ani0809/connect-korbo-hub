<x-setting-section title="Colors" description="Configure colors settings.">
    <div class='settings-grid'>
        <x-setting-text label="colors Title" name="colors__title" :value="\['colors']['colors__title'] ?? ''"/>
        <x-setting-toggle label="Enable colors" name="colors__enabled" :value="\['colors']['colors__enabled'] ?? 1"/>
        <x-setting-color label="colors Primary Color" name="colors__primary_color" :value="\['colors']['colors__primary_color'] ?? '#2563eb'"/>
        <x-setting-range label="colors Spacing" name="colors__spacing" min="0" max="100" :value="\['colors']['colors__spacing'] ?? 20"/>
        <x-setting-select label="colors Layout" name="colors__layout" :value="\['colors']['colors__layout'] ?? 'default'" :options="['default'=>'Default','boxed'=>'Boxed','full'=>'Full']"/>
    </div>
    <div class='settings-grid'>
        @for(\ = 1; \ <= 30; \++)
            <x-setting-text :label="'colors Option '.\" :name="'colors__opt_'.\" :value="\['colors']['colors__opt_'.\] ?? ''"/>
        @endfor
    </div>
</x-setting-section>
