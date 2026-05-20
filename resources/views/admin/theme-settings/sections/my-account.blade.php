<x-setting-section title="My account" description="Configure my account settings.">
    <div class='settings-grid'>
        <x-setting-text label="my account Title" name="my-account__title" :value="\['my-account']['my-account__title'] ?? ''"/>
        <x-setting-toggle label="Enable my account" name="my-account__enabled" :value="\['my-account']['my-account__enabled'] ?? 1"/>
        <x-setting-color label="my account Primary Color" name="my-account__primary_color" :value="\['my-account']['my-account__primary_color'] ?? '#2563eb'"/>
        <x-setting-range label="my account Spacing" name="my-account__spacing" min="0" max="100" :value="\['my-account']['my-account__spacing'] ?? 20"/>
        <x-setting-select label="my account Layout" name="my-account__layout" :value="\['my-account']['my-account__layout'] ?? 'default'" :options="['default'=>'Default','boxed'=>'Boxed','full'=>'Full']"/>
    </div>
    <div class='settings-grid'>
        @for(\ = 1; \ <= 30; \++)
            <x-setting-text :label="'my account Option '.\" :name="'my-account__opt_'.\" :value="\['my-account']['my-account__opt_'.\] ?? ''"/>
        @endfor
    </div>
</x-setting-section>
