@php
    use App\Support\StaffPermissions;
    $u = auth()->user();
    $admin = ($u->role ?? null) === 'admin';
    $can = fn (string $p): bool => StaffPermissions::has($u, $p);
    $settingsHubActive = request()->routeIs('admin.settings.index')
        || (
            request()->routeIs('admin.settings.*')
            && ! request()->routeIs('admin.settings.system*')
            && ! request()->routeIs('admin.settings.order*')
            && ! request()->routeIs('admin.settings.tax*')
            && ! request()->routeIs('admin.settings.customer*')
            && ! request()->routeIs('admin.settings.seller*')
            && ! request()->routeIs('admin.settings.units*')
            && ! request()->routeIs('admin.settings.cookie*')
            && ! request()->routeIs('admin.settings.maintenance*')
            && ! request()->routeIs('admin.settings.backup*')
        );
    $systemSettingsActive = request()->routeIs('admin.settings.system*')
        || request()->routeIs('admin.settings.order*')
        || request()->routeIs('admin.settings.tax*')
        || request()->routeIs('admin.settings.customer*')
        || request()->routeIs('admin.settings.seller*')
        || request()->routeIs('admin.settings.units*')
        || request()->routeIs('admin.settings.cookie*')
        || request()->routeIs('admin.settings.maintenance*')
        || request()->routeIs('admin.settings.backup*');
@endphp
<aside class="admin-sidebar zsb" :class="{ collapsed: !sidebarOpen, mobile: mobileOpen }">
    <div class="zsb-header">
        <a href="{{ route('admin.dashboard') }}" class="zsb-brand cc-brand-lockup" x-show="sidebarOpen" x-cloak>
            <span class="zsb-brand-mark">
                @if(setting('site_logo'))
                    <img src="{{ asset('storage/'.setting('site_logo')) }}" alt="{{ setting('site_name','Cibato Commerce') }}" class="w-full h-full object-cover rounded-full">
                @else
                    <x-admin.nav-icon name="store" class="text-[hsl(var(--primary-foreground))]" />
                @endif
            </span>
            <span class="zsb-brand-text cc-brand-wordmark">{{ setting('site_name', 'Cibato Commerce') }}</span>
        </a>
        <button type="button" class="zsb-toggle" @click="toggleSidebar()" :aria-label="sidebarOpen ? 'Collapse sidebar' : 'Expand sidebar'">
            <svg xmlns="http://www.w3.org/2000/svg" class="zsb-toggle-open" x-show="sidebarOpen" x-cloak viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M15 18 9 12l6-6"/></svg>
            <svg xmlns="http://www.w3.org/2000/svg" class="zsb-toggle-collapsed" x-show="!sidebarOpen" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M4 7h16"/><path d="M4 12h16"/><path d="M4 17h16"/></svg>
        </button>
    </div>

    <nav class="pretty-scroll zsb-nav">
        @if ($admin || $can('view_dashboard'))
            <x-admin.sidebar-leaf href="{{ route('admin.dashboard') }}" icon="dashboard" :active="request()->routeIs('admin.dashboard')">
                {{ __('Dashboard') }}
            </x-admin.sidebar-leaf>
        @endif

        @if ($admin || $can('view_products') || $can('view_categories') || $can('manage_categories') || $can('view_brands') || $can('manage_brands'))
            @php $gCat = request()->routeIs(['admin.products.*', 'admin.categories.*', 'admin.brands.*']); @endphp
            <x-admin.sidebar-group label="{{ __('Catalog') }}" icon="package" group-key="Catalog" :active="$gCat">
                @if ($admin || $can('view_products'))
                    <x-admin.sidebar-sub-link href="{{ route('admin.products.index') }}" :active="request()->routeIs('admin.products.*')">{{ __('Products') }}</x-admin.sidebar-sub-link>
                @endif
                @if ($admin || $can('view_categories') || $can('manage_categories'))
                    <x-admin.sidebar-sub-link href="{{ route('admin.categories.index') }}" :active="request()->routeIs('admin.categories.*')">{{ __('Categories') }}</x-admin.sidebar-sub-link>
                @endif
                @if ($admin || $can('view_brands') || $can('manage_brands'))
                    <x-admin.sidebar-sub-link href="{{ route('admin.brands.index') }}" :active="request()->routeIs('admin.brands.*')">{{ __('Brands') }}</x-admin.sidebar-sub-link>
                @endif
            </x-admin.sidebar-group>
        @endif

        @if ($admin || $can('view_orders') || $can('view_payments') || $can('manage_coupons') || $can('manage_flash_deals'))
            @php $gSales = request()->routeIs(['admin.orders.*', 'admin.payment.*', 'admin.payments.*', 'admin.coupons.*', 'admin.flash-deals.*']); @endphp
            <x-admin.sidebar-group label="{{ __('Sales') }}" icon="cart" group-key="Sales" :active="$gSales">
                @if ($admin || $can('view_orders'))
                    <x-admin.sidebar-sub-link href="{{ route('admin.orders.index') }}" :active="request()->routeIs('admin.orders.*')">{{ __('Orders') }}</x-admin.sidebar-sub-link>
                @endif
                @if ($admin || $can('view_payments') || $can('manage_payments'))
                    <x-admin.sidebar-sub-link href="{{ route('admin.payment.index') }}" :active="request()->routeIs('admin.payment.*') || request()->routeIs('admin.payments.*')">{{ __('Payments') }}</x-admin.sidebar-sub-link>
                @endif
                @if ($admin || $can('manage_coupons'))
                    <x-admin.sidebar-sub-link href="{{ route('admin.coupons.index') }}" :active="request()->routeIs('admin.coupons.*')">{{ __('Coupons') }}</x-admin.sidebar-sub-link>
                @endif
                @if ($admin || $can('manage_flash_deals'))
                    <x-admin.sidebar-sub-link href="{{ route('admin.flash-deals.index') }}" :active="request()->routeIs('admin.flash-deals.*')">{{ __('Flash Deals') }}</x-admin.sidebar-sub-link>
                @endif
            </x-admin.sidebar-group>
        @endif

        @if ($admin || $can('view_sellers') || $can('view_seller_earnings') || $can('manage_withdrawals'))
            @php $gMkt = request()->routeIs(['admin.sellers.*', 'admin.commission.*', 'admin.withdrawals.*']); @endphp
            <x-admin.sidebar-group label="{{ __('Marketplace') }}" icon="store" group-key="Marketplace" :active="$gMkt">
                @if ($admin || $can('view_sellers'))
                    <x-admin.sidebar-sub-link href="{{ route('admin.sellers.index') }}" :active="request()->routeIs('admin.sellers.*')">{{ __('Sellers') }}</x-admin.sidebar-sub-link>
                @endif
                @if ($admin || $can('view_seller_earnings'))
                    <x-admin.sidebar-sub-link href="{{ route('admin.commission.index') }}" :active="request()->routeIs('admin.commission.*')">{{ __('Commissions') }}</x-admin.sidebar-sub-link>
                @endif
                @if ($admin || $can('manage_withdrawals'))
                    <x-admin.sidebar-sub-link href="{{ route('admin.withdrawals.index') }}" :active="request()->routeIs('admin.withdrawals.*')">{{ __('Withdrawals') }}</x-admin.sidebar-sub-link>
                @endif
            </x-admin.sidebar-group>
        @endif

        @if ($admin || $can('view_customers'))
            <x-admin.sidebar-leaf href="{{ route('admin.customers.index') }}" icon="users" :active="request()->routeIs('admin.customers.*')">
                {{ __('Customers') }}
            </x-admin.sidebar-leaf>
        @endif

        @if ($admin || $can('view_reports'))
            @php $gAn = request()->routeIs(['admin.reports.*', 'admin.analytics.*']); @endphp
            <x-admin.sidebar-group label="{{ __('Analytics') }}" icon="chart" group-key="Analytics" :active="$gAn">
                <x-admin.sidebar-sub-link href="{{ route('admin.reports.sales') }}" :active="request()->routeIs('admin.reports.sales')">{{ __('Sales Report') }}</x-admin.sidebar-sub-link>
                <x-admin.sidebar-sub-link href="{{ route('admin.reports.products') }}" :active="request()->routeIs('admin.reports.products')">{{ __('Product Report') }}</x-admin.sidebar-sub-link>
                <x-admin.sidebar-sub-link href="{{ route('admin.reports.customers') }}" :active="request()->routeIs('admin.reports.customers')">{{ __('Customer Report') }}</x-admin.sidebar-sub-link>
                <x-admin.sidebar-sub-link href="{{ route('admin.analytics.index') }}" :active="request()->routeIs('admin.analytics.index')">{{ __('GA Analytics') }}</x-admin.sidebar-sub-link>
                <x-admin.sidebar-sub-link href="{{ route('admin.analytics.advanced') }}" :active="request()->routeIs('admin.analytics.advanced')">{{ __('Advanced analytics') }}</x-admin.sidebar-sub-link>
            </x-admin.sidebar-group>
        @endif

        @if ($admin || $can('send_newsletters'))
            @php $gMktg = request()->routeIs(['admin.newsletter.*', 'admin.promotions.*']); @endphp
            <x-admin.sidebar-group label="{{ __('Marketing') }}" icon="megaphone" group-key="Marketing" :active="$gMktg">
                @if ($admin || $can('send_newsletters'))
                    <x-admin.sidebar-sub-link href="{{ route('admin.newsletter.index') }}" :active="request()->routeIs('admin.newsletter.*')">{{ __('Newsletter') }}</x-admin.sidebar-sub-link>
                @endif
                @if ($admin)
                    <x-admin.sidebar-sub-link href="{{ route('admin.promotions.index') }}" :active="request()->routeIs('admin.promotions.*')">{{ __('Promotions') }}</x-admin.sidebar-sub-link>
                    <x-admin.sidebar-sub-link href="#">{{ __('Buy X Get Y') }}</x-admin.sidebar-sub-link>
                    <x-admin.sidebar-sub-link href="#">{{ __('Free Shipping Rules') }}</x-admin.sidebar-sub-link>
                    <x-admin.sidebar-sub-link href="#">{{ __('Push Notifications') }}</x-admin.sidebar-sub-link>
                    <x-admin.sidebar-sub-link href="#">{{ __('Analytics & Pixels') }}</x-admin.sidebar-sub-link>
                @endif
            </x-admin.sidebar-group>
        @endif

        @if ($admin || $can('manage_settings') || $can('view_products'))
            @php $gContent = request()->routeIs(['admin.pages.*', 'admin.faq.*', 'admin.contact.*', 'admin.translations.*', 'admin.import-export.*']); @endphp
            <x-admin.sidebar-group label="{{ __('Content') }}" icon="file" group-key="Content" :active="$gContent">
                <x-admin.sidebar-sub-link href="{{ route('admin.pages.index') }}" :active="request()->routeIs('admin.pages.*')">{{ __('Static Pages') }}</x-admin.sidebar-sub-link>
                <x-admin.sidebar-sub-link href="{{ route('admin.faq.index') }}" :active="request()->routeIs('admin.faq.*')">{{ __('FAQ') }}</x-admin.sidebar-sub-link>
                <x-admin.sidebar-sub-link href="{{ route('admin.contact.index') }}" :active="request()->routeIs('admin.contact.*')">{{ __('Contact Inbox') }}</x-admin.sidebar-sub-link>
                @if ($admin || $can('manage_settings'))
                    <x-admin.sidebar-sub-link href="{{ route('admin.translations.index') }}" :active="request()->routeIs('admin.translations.*')">{{ __('Translations') }}</x-admin.sidebar-sub-link>
                @endif
                @if ($admin || $can('view_products'))
                    <x-admin.sidebar-sub-link href="{{ route('admin.import-export.products') }}" :active="request()->routeIs('admin.import-export.*')">{{ __('Import / Export') }}</x-admin.sidebar-sub-link>
                @endif
            </x-admin.sidebar-group>
        @endif

        @if ($admin || $can('view_reviews') || $can('manage_qna'))
            <x-admin.sidebar-leaf href="{{ route('admin.reviews.index') }}" icon="messages" :active="request()->routeIs(['admin.reviews.*', 'admin.qna.*'])">
                {{ __('Reviews & Q&A') }}
            </x-admin.sidebar-leaf>
        @endif

        @if ($admin || $can('view_orders'))
            @php $gPos = request()->routeIs('admin.pos.*'); @endphp
            <x-admin.sidebar-group label="{{ __('POS') }}" icon="credit-card" group-key="POS" :active="$gPos">
                <x-admin.sidebar-sub-link href="{{ route('admin.pos.index') }}" :active="request()->routeIs('admin.pos.*')">{{ __('Open Terminal') }}</x-admin.sidebar-sub-link>
            </x-admin.sidebar-group>
        @endif

        @if ($admin)
            <x-admin.sidebar-group label="{{ __('Inventory') }}" icon="boxes" group-key="Inventory" :active="false">
                <x-admin.sidebar-sub-link href="#" :active="false">{{ __('Warehouses') }}</x-admin.sidebar-sub-link>
                <x-admin.sidebar-sub-link href="#" :active="false">{{ __('Stock Levels') }}</x-admin.sidebar-sub-link>
                <x-admin.sidebar-sub-link href="#" :active="false">{{ __('Adjustments') }}</x-admin.sidebar-sub-link>
                <x-admin.sidebar-sub-link href="#" :active="false">{{ __('Transfers') }}</x-admin.sidebar-sub-link>
                <x-admin.sidebar-sub-link href="#" :active="false">{{ __('Suppliers') }}</x-admin.sidebar-sub-link>
                <x-admin.sidebar-sub-link href="#" :active="false">{{ __('Purchase Orders') }}</x-admin.sidebar-sub-link>
            </x-admin.sidebar-group>
        @endif

        @if ($admin)
            @php $gAcc = request()->routeIs('admin.accounting.*'); @endphp
            <x-admin.sidebar-group label="{{ __('Accounting') }}" icon="calculator" group-key="Accounting" :active="$gAcc">
                <x-admin.sidebar-sub-link href="{{ route('admin.accounting.chart-of-accounts.index') }}" :active="request()->routeIs('admin.accounting.chart-of-accounts.*')">{{ __('Chart of Accounts') }}</x-admin.sidebar-sub-link>
                <x-admin.sidebar-sub-link href="{{ route('admin.accounting.journals.index') }}" :active="request()->routeIs('admin.accounting.journals.*')">{{ __('Journal') }}</x-admin.sidebar-sub-link>
                <x-admin.sidebar-sub-link href="{{ route('admin.accounting.reports.profit-loss') }}" :active="request()->routeIs('admin.accounting.reports.profit-loss')">{{ __('Profit & Loss') }}</x-admin.sidebar-sub-link>
                <x-admin.sidebar-sub-link href="{{ route('admin.accounting.reports.balance-sheet') }}" :active="request()->routeIs('admin.accounting.reports.balance-sheet')">{{ __('Balance Sheet') }}</x-admin.sidebar-sub-link>
                <x-admin.sidebar-sub-link href="{{ route('admin.accounting.expenses.index') }}" :active="request()->routeIs('admin.accounting.expenses.*')">{{ __('Expenses') }}</x-admin.sidebar-sub-link>
            </x-admin.sidebar-group>
        @endif

        @if ($admin)
            @php $gCour = request()->routeIs(['admin.settings.courier', 'admin.fraud.*']); @endphp
            <x-admin.sidebar-group label="{{ __('Courier') }}" icon="truck" group-key="Courier" :active="$gCour">
                <x-admin.sidebar-sub-link href="{{ route('admin.settings.courier') }}" :active="request()->routeIs('admin.settings.courier')">{{ __('Integrations') }}</x-admin.sidebar-sub-link>
                <x-admin.sidebar-sub-link href="{{ route('admin.fraud.dashboard') }}" :active="request()->routeIs('admin.fraud.*')">{{ __('Fraud Checker') }}</x-admin.sidebar-sub-link>
                <x-admin.sidebar-sub-link href="#">{{ __('Auto-Block Rules') }}</x-admin.sidebar-sub-link>
            </x-admin.sidebar-group>
        @endif

        @if ($admin || $can('manage_settings'))
            @php $gApp = request()->routeIs(['admin.theme-settings.*', 'admin.builder.*', 'admin.layout-builder.*', 'admin.menus.*', 'admin.emails.*']); @endphp
            <x-admin.sidebar-group label="{{ __('Appearance') }}" icon="palette" group-key="Appearance" :active="$gApp">
                <x-admin.sidebar-sub-link href="{{ route('admin.settings.index') }}" :active="$settingsHubActive">{{ __('Overview') }}</x-admin.sidebar-sub-link>
                <x-admin.sidebar-sub-link href="{{ route('admin.theme-settings') }}" :active="request()->routeIs('admin.theme-settings.*')">{{ __('Theme Settings') }}</x-admin.sidebar-sub-link>
                <x-admin.sidebar-sub-link href="{{ route('admin.layout-builder.index') }}" :active="request()->routeIs('admin.layout-builder.*')">{{ __('Templates Gallery') }}</x-admin.sidebar-sub-link>
                <x-admin.sidebar-sub-link href="{{ route('admin.menus.index') }}" :active="request()->routeIs('admin.menus.*')">{{ __('Menu Builder') }}</x-admin.sidebar-sub-link>
                <x-admin.sidebar-sub-link href="{{ route('admin.builder.header') }}" :active="request()->routeIs('admin.builder.header')">{{ __('Header Builder') }}</x-admin.sidebar-sub-link>
                <x-admin.sidebar-sub-link href="{{ route('admin.builder.footer') }}" :active="request()->routeIs('admin.builder.footer')">{{ __('Footer Builder') }}</x-admin.sidebar-sub-link>
                <x-admin.sidebar-sub-link href="{{ route('admin.builder.homepage') }}" :active="request()->routeIs('admin.builder.homepage')">{{ __('Homepage Builder') }}</x-admin.sidebar-sub-link>
                <x-admin.sidebar-sub-link href="{{ route('admin.builder.product-card') }}" :active="request()->routeIs('admin.builder.product-card')">{{ __('Product Card') }}</x-admin.sidebar-sub-link>
                <x-admin.sidebar-sub-link href="#">{{ __('Invoice Designer') }}</x-admin.sidebar-sub-link>
                <x-admin.sidebar-sub-link href="{{ route('admin.emails.index') }}" :active="request()->routeIs('admin.emails.*')">{{ __('Email Templates') }}</x-admin.sidebar-sub-link>
            </x-admin.sidebar-group>
        @endif

        @if ($admin || $can('manage_settings'))
            <x-admin.sidebar-leaf href="{{ route('admin.migration.index') }}" icon="database" :active="request()->routeIs('admin.migration.*')">
                {{ __('Migration') }}
            </x-admin.sidebar-leaf>
        @endif

        @if ($admin || $can('view_settings') || $can('manage_settings'))
            <x-admin.sidebar-leaf href="{{ route('admin.settings.index') }}" icon="settings" :active="$settingsHubActive || $systemSettingsActive || request()->routeIs('admin.staff.*')">
                {{ __('Settings') }}
            </x-admin.sidebar-leaf>
        @endif
    </nav>

    <div class="zsb-footer" x-show="sidebarOpen" x-cloak>
        <p>&copy; {{ date('Y') }} {{ setting('site_name', 'Cibato Commerce') }} · v{{ config('shop.version') }}</p>
    </div>
</aside>
