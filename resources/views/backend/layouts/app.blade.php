<!doctype html>
@if (\App\Models\Language::where('code', Session::get('locale', Config::get('app.locale')))->first()->rtl == 1)
    <html dir="rtl" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@else
    <html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@endif

<head>
    @php
        $assetVersion = get_setting('current_version') ?: '1.0.0';
    @endphp
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="app-url" content="{{ getBaseURL() }}">
    <meta name="file-base-url" content="{{ getFileBaseURL() }}">

    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Favicon -->
    <link rel="icon" href="{{ uploaded_asset(get_setting('site_icon')) }}">
    <link rel="apple-touch-icon" href="{{ uploaded_asset(get_setting('site_icon')) }}">
    <title>{{ get_setting('website_name') . ' | ' . get_setting('site_motto') }}</title>

    <!-- google font -->
    {{-- <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700"> --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap">

    <!-- Cibato core css -->
    <link rel="stylesheet" href="{{ static_asset('assets/css/vendors.css?v=') }}{{ $assetVersion }}">
    @if (\App\Models\Language::where('code', Session::get('locale', Config::get('app.locale')))->first()->rtl == 1)
        <link rel="stylesheet" href="{{ static_asset('assets/css/bootstrap-rtl.min.css') }}">
    @endif
    <link rel="stylesheet" href="{{ static_asset('assets/css/cibato-core.css?v=') }}{{ $assetVersion }}">
    <link rel="stylesheet" href="{{ static_asset('assets/css/custom-style.css?v=') }}{{ $assetVersion }}">

    <style>
        :root {
            --blue: #3390f3;
            --hov-blue: #1f6dc2;
            --soft-blue: #f1fafd;

            --primary: #009ef7;
            --hov-primary: #008cdd;
            --soft-primary: #f1fafd;
            --secondary: #a1a5b3;
            --soft-secondary: rgba(143, 151, 171, 0.15);
            --success: #19c553;
            --hov-success: #16a846;
            --soft-success:  #e6fff3;
            --info: #8f60ee;
            --hov-info: #714cbd;
            --soft-info: #f4effe;
            --warning: #ffc700;
            --soft-warning: #fff9e3;
            --danger: #F0416C;
            --soft-danger: #fff4f8;
            --dark: #232734;
            --soft-dark: #1b2133;

            --secondary-base: #f1416c;
            --hov-secondary-base: #c73459;
            --soft-secondary-base: rgb(241, 65, 108, 0.15);
        }
        body {
            font-size: 12px;
            font-family: {!! !empty(get_setting('system_font_family')) ? get_setting('system_font_family') : "'Public Sans', sans-serif" !!}, sans-serif;
        }
        /* .bootstrap-select .btn,
        .btn:not(.btn-circle),
        .form-control,
        .input-group-text,
        .custom-file-label, .custom-file-label::after {
            border-radius: 0;
        } */
        .border-gray {
            border-color: #e4e5eb !important;
        }
        .card {
            border-radius: 8px;
            background: #fff;
            border: 1px solid #f1f1f4;
            box-shadow: 0px 6px 14px rgba(35, 39, 52, 0.04);
        }
        .form-control {
            border: 1px solid #e4e5eb;
        }
        .cibato-color-input{
            border-top-left-radius: 4px !important;
            border-bottom-left-radius: 4px !important;
        }
        .form-control.file-amount{
            border-top-right-radius: 4px !important;
            border-bottom-right-radius: 4px !important;
        }
        /* Reference-inspired clean sidebar v2 */
        .cibato-sidebar-wrap,
        .cibato-sidebar {
            background: #111827 !important;
            border-right: 1px solid #1f2937 !important;
            box-shadow: none !important;
        }
        .cibato-sidebar.left {
            width: 286px;
        }
        .cibato-side-nav-logo-wrap {
            background: #111827 !important;
            border-bottom: 0 !important;
        }
        .cibato-side-nav-logo-wrap a {
            padding: 18px 22px 10px;
        }
        .cibato-side-nav-logo-wrap img {
            height: 34px;
        }
        .cibato-side-nav-wrap {
            padding: 10px 0 14px;
        }
        .cibato-side-nav-wrap .position-relative {
            padding: 0 14px;
            margin-bottom: 12px !important;
        }
        .cibato-side-nav-wrap #menu-search {
            border: 1px solid #2a3444 !important;
            background: #0f172a !important;
            color: #e5ecf9 !important;
            border-radius: 9px;
            height: 34px;
        }
        .cibato-side-nav-wrap #menu-search::placeholder {
            color: #a6b2c9;
        }
        .cibato-side-nav-wrap #menu-search:focus {
            border-color: #3d6ef5 !important;
            box-shadow: 0 0 0 3px rgba(61, 110, 245, 0.12) !important;
        }
        .cibato-side-nav-list .cibato-side-nav-item {
            margin: 0;
        }
        .cibato-side-nav-list .cibato-side-nav-link {
            margin: 2px 12px;
            padding: 8px 11px;
            border-radius: 10px;
            color: #e4ebf7;
            font-weight: 500;
            background: transparent !important;
            border: 1px solid transparent;
            transition: background-color .18s ease, color .18s ease, border-color .18s ease;
        }
        .cibato-side-nav-list .cibato-side-nav-link:hover {
            background: #1f2937 !important;
            border-color: #334155;
            color: #ffffff;
        }
        .cibato-side-nav-list .cibato-side-nav-icon {
            width: 24px;
            min-width: 24px;
            height: 22px;
            margin-right: 8px;
            justify-content: center;
            align-items: center;
            border-radius: 7px;
            background: #1f2937;
        }
        .cibato-side-nav-list .cibato-side-nav-icon i {
            font-size: 13px;
            color: #d4dded;
            opacity: .86;
        }
        .cibato-side-nav-list .cibato-side-nav-icon svg {
            display: none !important;
        }
        .cibato-side-nav-list .cibato-side-nav-text {
            color: inherit;
            font-size: 12.5px;
        }
        .cibato-side-nav-list .cibato-side-nav-arrow::after {
            color: #aab6cd;
            font-size: 11px;
            opacity: 1;
            font-family: "Line Awesome Free";
            font-weight: 900;
        }
        .cibato-side-nav-list .cibato-side-nav-link:hover .cibato-side-nav-arrow::after,
        .cibato-side-nav-list .cibato-side-nav-link.active .cibato-side-nav-arrow::after,
        .cibato-side-nav-list .cibato-side-nav-link.level-2-active .cibato-side-nav-arrow::after,
        .cibato-side-nav-list .cibato-side-nav-link.level-3-active .cibato-side-nav-arrow::after,
        .cibato-side-nav-list .cibato-side-nav-item.mm-active > .cibato-side-nav-link .cibato-side-nav-arrow::after {
            color: #ffffff !important;
        }
        .cibato-side-nav-list .cibato-side-nav-link .cibato-side-nav-arrow {
            min-width: 20px;
            display: inline-flex;
            justify-content: center;
            align-items: center;
            border-radius: 999px;
            transition: background-color .18s ease;
        }
        .cibato-side-nav-list .cibato-side-nav-link:hover .cibato-side-nav-arrow {
            background: transparent;
        }
        .cibato-side-nav-list .cibato-side-nav-link.active,
        .cibato-side-nav-list .cibato-side-nav-link.level-2-active,
        .cibato-side-nav-list .cibato-side-nav-link.level-3-active,
        .cibato-side-nav-list .cibato-side-nav-item.mm-active > .cibato-side-nav-link {
            background: #1f2937 !important;
            border-color: #334155;
            color: #ffffff !important;
            box-shadow: none;
        }
        .cibato-side-nav-list .cibato-side-nav-link.active .cibato-side-nav-icon i,
        .cibato-side-nav-list .cibato-side-nav-link.level-2-active .cibato-side-nav-icon i,
        .cibato-side-nav-list .cibato-side-nav-link.level-3-active .cibato-side-nav-icon i,
        .cibato-side-nav-list .cibato-side-nav-item.mm-active > .cibato-side-nav-link .cibato-side-nav-icon i {
            color: #ffffff;
            opacity: 1;
        }
        .cibato-side-nav-list .cibato-side-nav-link.active .cibato-side-nav-icon,
        .cibato-side-nav-list .cibato-side-nav-link.level-2-active .cibato-side-nav-icon,
        .cibato-side-nav-list .cibato-side-nav-link.level-3-active .cibato-side-nav-icon,
        .cibato-side-nav-list .cibato-side-nav-item.mm-active > .cibato-side-nav-link .cibato-side-nav-icon {
            background: rgba(255, 255, 255, 0.16);
        }
        .cibato-side-nav-list .level-2 .cibato-side-nav-link {
            margin-left: 24px;
            margin-right: 12px;
            padding: 7px 10px;
            font-size: 12px;
            color: #d2dced;
            border-radius: 8px;
            background: transparent !important;
            border: 0;
            box-shadow: none;
        }
        .cibato-side-nav-list .level-2 .cibato-side-nav-link:after {
            display: none;
        }
        .cibato-side-nav-list .level-3 .cibato-side-nav-link {
            margin-left: 34px;
            padding: 6px 9px;
            font-size: 11.5px;
            background: transparent !important;
            border: 0;
            color: #c8d4e7;
        }
        .cibato-side-nav-list .level-2 .cibato-side-nav-link:hover,
        .cibato-side-nav-list .level-3 .cibato-side-nav-link:hover {
            background: #1f2937 !important;
        }
        .cibato-side-nav-list .level-2 .cibato-side-nav-icon,
        .cibato-side-nav-list .level-3 .cibato-side-nav-icon {
            width: 20px;
            min-width: 20px;
            height: 20px;
            border-radius: 6px;
            background: #1f2937;
        }
        .cibato-side-nav-list .level-2 .cibato-side-nav-icon i,
        .cibato-side-nav-list .level-3 .cibato-side-nav-icon i {
            font-size: 11px;
        }
        .cibato-side-nav-list .cibato-side-nav-item > .level-2 {
            margin: 2px 10px 6px;
            padding: 1px 0 3px;
            border-left: 1px solid #334155;
            background: transparent;
            border-radius: 0;
        }
        [dir="rtl"] .cibato-side-nav-list .cibato-side-nav-item > .level-2 {
            border-left: 0;
            border-right: 2px solid #334155;
        }
        .cibato-side-nav-list .level-2 .cibato-side-nav-link.active,
        .cibato-side-nav-list .level-2 .cibato-side-nav-link.level-2-active,
        .cibato-side-nav-list .level-3 .cibato-side-nav-link.active,
        .cibato-side-nav-list .level-3 .cibato-side-nav-link.level-3-active {
            background: #1f2937 !important;
            border-color: #334155;
            color: #ffffff !important;
            box-shadow: none;
        }
        .cibato-side-nav-list .level-2 .cibato-side-nav-link.active .cibato-side-nav-icon,
        .cibato-side-nav-list .level-2 .cibato-side-nav-link.level-2-active .cibato-side-nav-icon,
        .cibato-side-nav-list .level-3 .cibato-side-nav-link.active .cibato-side-nav-icon,
        .cibato-side-nav-list .level-3 .cibato-side-nav-link.level-3-active .cibato-side-nav-icon {
            background: rgba(255, 255, 255, 0.16);
        }
        .cibato-side-nav-list .level-2 .cibato-side-nav-link.active .cibato-side-nav-icon i,
        .cibato-side-nav-list .level-2 .cibato-side-nav-link.level-2-active .cibato-side-nav-icon i,
        .cibato-side-nav-list .level-3 .cibato-side-nav-link.active .cibato-side-nav-icon i,
        .cibato-side-nav-list .level-3 .cibato-side-nav-link.level-3-active .cibato-side-nav-icon i {
            color: #ffffff;
        }
        .cibato-side-nav-list .level-2 .cibato-side-nav-link.active .cibato-side-nav-arrow::after,
        .cibato-side-nav-list .level-2 .cibato-side-nav-link.level-2-active .cibato-side-nav-arrow::after,
        .cibato-side-nav-list .level-3 .cibato-side-nav-link.active .cibato-side-nav-arrow::after,
        .cibato-side-nav-list .level-3 .cibato-side-nav-link.level-3-active .cibato-side-nav-arrow::after {
            color: #ffffff !important;
        }
        [dir="rtl"] .cibato-side-nav-list .cibato-side-nav-icon {
            margin-left: 8px;
            margin-right: 0;
        }
        [dir="rtl"] .cibato-side-nav-list .level-2 .cibato-side-nav-link {
            margin-right: 24px;
            margin-left: 12px;
        }
        [dir="rtl"] .cibato-side-nav-list .level-3 .cibato-side-nav-link {
            margin-right: 34px;
            margin-left: 12px;
        }
        /* Topbar aligned with dark sidebar */
        .cibato-topbar {
            background: #ffffff !important;
            border-bottom: 1px solid #e6ebf2;
        }
        .cibato-topbar .btn-topbar.btn-light,
        .cibato-topbar .btn-topbar {
            background: #f5f7fb !important;
            border: 1px solid #e2e8f0 !important;
            color: #6b7280 !important;
            box-shadow: none !important;
            border-radius: 9px !important;
        }
        .cibato-topbar .btn-topbar.btn-icon,
        .cibato-topbar-nav-toggler .btn-topbar {
            width: 34px;
            height: 34px;
            padding: 0 !important;
            display: inline-flex !important;
            align-items: center;
            justify-content: center;
        }
        .cibato-topbar .btn-topbar svg path,
        .cibato-topbar .btn-topbar svg rect,
        .cibato-topbar .btn-topbar svg circle {
            fill: #8b96a8 !important;
        }
        .cibato-topbar .btn-topbar:hover {
            background: #eef2f7 !important;
            border-color: #d5deea !important;
        }
        .cibato-topbar .cibato-topbar-menu {
            color: #b9c8e4 !important;
            border-bottom: 2px solid transparent;
            padding: 0 14px;
        }
        .cibato-topbar .cibato-topbar-menu:hover {
            color: #ffffff !important;
        }
        .cibato-topbar .cibato-topbar-menu.active {
            color: #ffffff !important;
            border-bottom-color: #009EF7;
        }
        .cibato-topbar .btn-soft-blue {
            background: #009EF7 !important;
            color: #ffffff !important;
            border: 1px solid #0087d4 !important;
        }
        .cibato-topbar .dropdown-toggle.no-arrow.text-dark,
        .cibato-topbar .dropdown-toggle.no-arrow.text-dark span,
        .cibato-topbar .fw-500 {
            color: #1f2937 !important;
        }
        .cibato-topbar .small.opacity-60 {
            color: #8b96a8 !important;
        }
        .cibato-topbar .size-40px {
            border: 1px solid #dde5f0;
        }
        .cibato-topbar > .d-flex:first-child {
            margin-right: 4px;
        }
        .cibato-topbar .cibato-topbar-item {
            margin-right: 8px !important;
        }
    </style>
    <script>
        var CIBATO = CIBATO || {};
        CIBATO.local = {
            nothing_selected: '{!! translate('Nothing selected', null, true) !!}',
            nothing_found: '{!! translate('Nothing found', null, true) !!}',
            choose_file: '{{ translate('Choose file') }}',
            file_selected: '{{ translate('File selected') }}',
            files_selected: '{{ translate('Files selected') }}',
            add_more_files: '{{ translate('Add more files') }}',
            adding_more_files: '{{ translate('Adding more files') }}',
            drop_files_here_paste_or: '{{ translate('Drop files here, paste or') }}',
            browse: '{{ translate('Browse') }}',
            upload_complete: '{{ translate('Upload complete') }}',
            upload_paused: '{{ translate('Upload paused') }}',
            resume_upload: '{{ translate('Resume upload') }}',
            pause_upload: '{{ translate('Pause upload') }}',
            retry_upload: '{{ translate('Retry upload') }}',
            cancel_upload: '{{ translate('Cancel upload') }}',
            uploading: '{{ translate('Uploading') }}',
            processing: '{{ translate('Processing') }}',
            complete: '{{ translate('Complete') }}',
            file: '{{ translate('File') }}',
            files: '{{ translate('Files') }}',
            saving: '{{ translate('Saving') }}',
            something_went_wrong: '{{translate('Something went wrong!')}}',
            error_occured_while_processing: '{{translate('An error occurred while processing')}}',
            saving_as_draft: '{{translate('Saving As Draft')}}',
        }
    </script>

</head>

<body class="">
    <div class="cibato-main-wrapper">
        @include('backend.inc.admin_sidenav')
        <div class="cibato-content-wrapper bg-white">
            @include('backend.inc.admin_nav')
            <div class="cibato-main-content">
                <div class="px-15px px-lg-25px">
                    @yield('content')
                </div>
                <div class="bg-white text-center py-3 px-15px px-lg-25px mt-auto border-top">
                    <p class="mb-0">&copy; {{ get_setting('site_name') }} v{{ get_setting('current_version') }}</p>
                </div>
            </div><!-- .cibato-main-content -->
        </div><!-- .cibato-content-wrapper -->
    </div><!-- .cibato-main-wrapper -->

    
    <!-- Bulk Action modal -->
    @include('modals.bulk_action_modal')
    @yield('modal')


    <script src="{{ static_asset('assets/js/vendors.js?v=') }}{{ $assetVersion }}"></script>
    <script src="{{ static_asset('assets/js/cibato-core.js?v=') }}{{ $assetVersion }}"></script>
    <script src="{{ static_asset('assets/js/cibato-form-submission.js?v=') }}{{ $assetVersion }}"></script>

    @yield('script')

    <script type="text/javascript">
        @foreach (session('flash_notification', collect())->toArray() as $message)
            CIBATO.plugins.notify('{{ $message['level'] }}', '{{ $message['message'] }}');
            @if ($message['message'] == translate('Product has been inserted successfully'))
                var data_type = ['digital', 'physical', 'auction', 'wholesale'];
                data_type.forEach(element => {
                    localStorage.setItem('tempdataproduct_'+element, '{}');
                    localStorage.setItem('tempload_'+element, 'no');
                });
            @endif
        @endforeach

        $('.dropdown-menu a[data-toggle="tab"]').click(function(e) {
            e.stopPropagation()
            $(this).tab('show')
        })

        if ($('#lang-change').length > 0) {
            $('#lang-change .dropdown-menu a').each(function() {
                $(this).on('click', function(e) {
                    e.preventDefault();
                    var $this = $(this);
                    var locale = $this.data('flag');
                    $.post('{{ route('language.change') }}', {
                        _token: '{{ csrf_token() }}',
                        locale: locale
                    }, function(data) {
                        location.reload();
                    });

                });
            });
        }

        function menuSearch() {
            var filter, item;
            filter = $("#menu-search").val().toUpperCase();
            items = $("#main-menu").find("a");
            items = items.filter(function(i, item) {
                if ($(item).find(".cibato-side-nav-text")[0].innerText.toUpperCase().indexOf(filter) > -1 && $(item)
                    .attr('href') !== '#') {
                    return item;
                }
            });

            if (filter !== '') {
                $("#main-menu").addClass('d-none');
                $("#search-menu").html('')
                if (items.length > 0) {
                    for (i = 0; i < items.length; i++) {
                        const text = $(items[i]).find(".cibato-side-nav-text")[0].innerText;
                        const link = $(items[i]).attr('href');
                        $("#search-menu").append(
                            `<li class="cibato-side-nav-item"><a href="${link}" class="cibato-side-nav-link"><i class="las la-ellipsis-h cibato-side-nav-icon"></i><span>${text}</span></a></li`
                            );
                    }
                } else {
                    $("#search-menu").html(
                        `<li class="cibato-side-nav-item"><span	class="text-center text-muted d-block">{{ translate('Nothing Found') }}</span></li>`
                        );
                }
            } else {
                $("#main-menu").removeClass('d-none');
                $("#search-menu").html('')
            }
        }
    </script>
    <script>
        (function () {
            var iconMap = {
                "Dashboard": "las la-th-large",
                "Products": "las la-shopping-basket",
                "Sales": "las la-chart-line",
                "Customers": "las la-user-friends",
                "Sellers": "las la-store",
                "Reports": "las la-chart-bar",
                "Marketing": "las la-bullhorn",
                "Marketing Analytics": "las la-signal",
                "Payment Gateways": "las la-wallet",
                "Website Setup": "las la-globe",
                "Setup & Configurations": "las la-sliders-h",
                "Staffs": "las la-user-tie",
                "System": "las la-cog",
                "Support": "las la-life-ring",
                "Uploaded Files": "las la-photo-video",
                "POS System": "las la-cash-register",
                "Notes": "las la-sticky-note",
                "AI Studio": "las la-robot",
                "Affiliate System": "las la-handshake",
                "Delivery Boy": "las la-truck",
                "Club Point System": "las la-gem",
                "Preorder": "las la-clock",
                "Wholesale Products": "las la-boxes",
                "Classified Products": "las la-tags",
                "All Products": "las la-cubes",
                "Add New product": "las la-plus-square",
                "Add New Preorder products": "las la-plus-square",
                "Add New auction product": "las la-plus-square",
                "Add New Wholesale Product": "las la-plus-square",
                "Category": "las la-sitemap",
                "Brand": "las la-award",
                "All Brands": "las la-award",
                "Brand Bulk Import": "las la-file-import",
                "Bulk Export": "las la-file-export",
                "Attribute": "las la-sliders-h",
                "Colors": "las la-palette",
                "Uploaded Files": "las la-photo-video",
                "Flash deals": "las la-bolt",
                "Dynamic Pop-up": "las la-window-maximize",
                "Email Templates": "las la-envelope-open-text",
                "Notification": "las la-bell",
                "Notification Types": "las la-bell",
                "Custom Notification": "las la-bell",
                "Custom Notification History": "las la-history",
                "Coupon": "las la-ticket-alt",
                "Newsletters": "las la-envelope",
                "Subscribers": "las la-users",
                "Custom Visitors": "las la-eye",
                "Size Guide": "las la-ruler-combined",
                "Size Chart": "las la-table",
                "Measurement Points": "las la-drafting-compass",
                "Warranty": "las la-shield-alt",
                "Product Reviews": "las la-star",
                "Seller Orders": "las la-shopping-cart",
                "Unpaid Orders": "las la-money-check-alt",
                "POS Orders": "las la-receipt",
                "POS Products": "las la-cubes",
                "POS Configuration": "las la-cogs",
                "POS Manager": "las la-cash-register",
                "All Orders": "las la-receipt",
                "Payment Histories": "las la-history",
                "Collected Histories": "las la-coins",
                "Cancel Request": "las la-times-circle",
                "Refund Requests": "las la-undo-alt",
                "Approved Refunds": "las la-check-circle",
                "Rejected Refunds": "las la-ban",
                "Refund Configuration": "las la-cog",
                "Google Analytics (GA4)": "lab la-google",
                "Google Analytics Reports": "las la-chart-pie",
                "Google TAG Manager": "las la-tags",
                "Meta Pixel": "lab la-facebook",
                "Meta Conversion API": "las la-random",
                "Ticket": "las la-ticket-alt",
                "Product Conversations": "las la-comments",
                "Affiliate Users": "las la-users",
                "Referral Users": "las la-user-friends",
                "Affiliate Logs": "las la-clipboard-list",
                "OTP Login Configuration": "las la-mobile-alt",
                "OTP Configurations": "las la-shield-alt",
                "SMS Templates": "las la-sms",
                "Payment Methods": "las la-credit-card",
                "Manual Payment Methods": "las la-hand-holding-usd",
                "Offline Payment Orders": "las la-file-invoice-dollar",
                "Offline Wallet Recharge": "las la-wallet",
                "Select Homepage": "las la-home",
                "Homepage Settings": "las la-cog",
                "Font Family": "las la-font",
                "Element": "las la-shapes"
            };

            function getLabel(node) {
                if (!node) return "";
                return (node.textContent || "").replace(/\s+/g, " ").trim();
            }

            function resolveIconByKeyword(label, isSubmenu) {
                var t = (label || "").toLowerCase();
                if (t.indexOf("add new") !== -1 || t.indexOf("create") !== -1) return "las la-plus-square";
                if (t.indexOf("order") !== -1) return "las la-receipt";
                if (t.indexOf("product") !== -1) return "las la-cubes";
                if (t.indexOf("category") !== -1) return "las la-sitemap";
                if (t.indexOf("brand") !== -1) return "las la-award";
                if (t.indexOf("color") !== -1) return "las la-palette";
                if (t.indexOf("size") !== -1 || t.indexOf("measurement") !== -1) return "las la-ruler-combined";
                if (t.indexOf("payment") !== -1 || t.indexOf("wallet") !== -1) return "las la-wallet";
                if (t.indexOf("shipping") !== -1 || t.indexOf("delivery") !== -1) return "las la-truck";
                if (t.indexOf("coupon") !== -1 || t.indexOf("discount") !== -1) return "las la-ticket-alt";
                if (t.indexOf("newsletter") !== -1 || t.indexOf("mail") !== -1 || t.indexOf("email") !== -1) return "las la-envelope";
                if (t.indexOf("notification") !== -1) return "las la-bell";
                if (t.indexOf("report") !== -1 || t.indexOf("analytics") !== -1) return "las la-chart-line";
                if (t.indexOf("setting") !== -1 || t.indexOf("configuration") !== -1) return "las la-cog";
                if (t.indexOf("seller") !== -1 || t.indexOf("staff") !== -1 || t.indexOf("customer") !== -1 || t.indexOf("user") !== -1) return "las la-users";
                if (t.indexOf("subscriber") !== -1 || t.indexOf("visitor") !== -1) return "las la-user";
                if (t.indexOf("support") !== -1 || t.indexOf("ticket") !== -1) return "las la-life-ring";
                if (t.indexOf("upload") !== -1 || t.indexOf("file") !== -1 || t.indexOf("template") !== -1) return "las la-file-alt";
                return isSubmenu ? "las la-file-alt" : "las la-th-large";
            }

            function applySidebarIcons() {
                document.querySelectorAll('#main-menu .cibato-side-nav-link').forEach(function (link) {
                    var textNode = link.querySelector('.cibato-side-nav-text');
                    if (!textNode) return;
                    var iconWrap = link.querySelector('.cibato-side-nav-icon');
                    if (!iconWrap) {
                        iconWrap = document.createElement('div');
                        iconWrap.className = 'cibato-side-nav-icon';
                    }
                    // Force icon position to the left of label.
                    link.insertBefore(iconWrap, textNode);

                    var label = getLabel(textNode);
                    var iconClass = iconMap[label];
                    var isSubmenu = !!(link.closest('.level-2') || link.closest('.level-3'));
                    if (!iconClass) iconClass = resolveIconByKeyword(label, isSubmenu);
                    iconWrap.innerHTML = '<i class="' + iconClass + '" aria-hidden="true"></i>';
                });

                // Ensure each parent menu with submenu has visible toggle arrow.
                document.querySelectorAll('#main-menu .cibato-side-nav-link').forEach(function (link) {
                    var hasSubmenu = link.nextElementSibling && link.nextElementSibling.classList.contains('cibato-side-nav-list');
                    if (!hasSubmenu) return;
                    var arrow = link.querySelector('.cibato-side-nav-arrow');
                    if (!arrow) {
                        arrow = document.createElement('span');
                        arrow.className = 'cibato-side-nav-arrow';
                        link.appendChild(arrow);
                    }
                });
            }

            document.addEventListener('DOMContentLoaded', applySidebarIcons);
            window.addEventListener('load', applySidebarIcons);
        })();
    </script>
</body>

</html>
