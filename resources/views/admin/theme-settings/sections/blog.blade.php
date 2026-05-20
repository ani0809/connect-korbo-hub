<x-setting-section title="Blog" description="Configure blog settings.">
    <div class='settings-grid'>
        <x-setting-text label="blog Title" name="blog__title" :value="\['blog']['blog__title'] ?? ''"/>
        <x-setting-toggle label="Enable blog" name="blog__enabled" :value="\['blog']['blog__enabled'] ?? 1"/>
        <x-setting-color label="blog Primary Color" name="blog__primary_color" :value="\['blog']['blog__primary_color'] ?? '#2563eb'"/>
        <x-setting-range label="blog Spacing" name="blog__spacing" min="0" max="100" :value="\['blog']['blog__spacing'] ?? 20"/>
        <x-setting-select label="blog Layout" name="blog__layout" :value="\['blog']['blog__layout'] ?? 'default'" :options="['default'=>'Default','boxed'=>'Boxed','full'=>'Full']"/>
    </div>
    <div class='settings-grid'>
        @for(\ = 1; \ <= 30; \++)
            <x-setting-text :label="'blog Option '.\" :name="'blog__opt_'.\" :value="\['blog']['blog__opt_'.\] ?? ''"/>
        @endfor
    </div>
</x-setting-section>
