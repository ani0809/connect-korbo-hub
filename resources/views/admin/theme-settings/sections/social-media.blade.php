<x-setting-section title="Social media" description="Configure social media settings.">
    <div class='settings-grid'>
        <x-setting-text label="social media Title" name="social-media__title" :value="\['social-media']['social-media__title'] ?? ''"/>
        <x-setting-toggle label="Enable social media" name="social-media__enabled" :value="\['social-media']['social-media__enabled'] ?? 1"/>
        <x-setting-color label="social media Primary Color" name="social-media__primary_color" :value="\['social-media']['social-media__primary_color'] ?? '#2563eb'"/>
        <x-setting-range label="social media Spacing" name="social-media__spacing" min="0" max="100" :value="\['social-media']['social-media__spacing'] ?? 20"/>
        <x-setting-select label="social media Layout" name="social-media__layout" :value="\['social-media']['social-media__layout'] ?? 'default'" :options="['default'=>'Default','boxed'=>'Boxed','full'=>'Full']"/>
    </div>
    <div class='settings-grid'>
        @for(\ = 1; \ <= 30; \++)
            <x-setting-text :label="'social media Option '.\" :name="'social-media__opt_'.\" :value="\['social-media']['social-media__opt_'.\] ?? ''"/>
        @endfor
    </div>
</x-setting-section>
