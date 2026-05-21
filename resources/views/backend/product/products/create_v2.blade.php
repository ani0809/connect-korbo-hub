@extends('backend.layouts.app')

@section('content')
    @php
        $v2MarkupCandidates = array_values(array_unique(array_filter([
            resource_path('views/backend/product/products/create_v2_embed.html'),
            storage_path('app/product_create_v2.html'),
            env('PRODUCT_CREATE_V2_HTML_PATH'),
            'C:\\Users\\Cibato\\Downloads\\cibato_add_product_v4_final.html',
        ])));

        $v2Html = null;
        foreach ($v2MarkupCandidates as $v2MarkupPath) {
            if (!empty($v2MarkupPath) && @is_readable($v2MarkupPath)) {
                $loaded = @file_get_contents($v2MarkupPath);
                if ($loaded !== false && trim($loaded) !== '') {
                    $v2Html = $loaded;
                    break;
                }
            }
        }

        if ($v2Html === null) {
            $v2Html = '<div class="alert alert-warning m-3">'
                . '<p class="fw-700 mb-2">' . e(translate('Add product (V2) template file was not found.')) . '</p>'
                . '<p class="mb-1 text-muted fs-13">' . e(translate('Save your markup as:'))
                . ' <code>storage/app/product_create_v2.html</code> '
                . e(translate('or place it at'))
                . ' <code>resources/views/backend/product/products/create_v2_embed.html</code>. '
                . e(translate('Optional:'))
                . ' <code>PRODUCT_CREATE_V2_HTML_PATH</code> '
                . e(translate('in .env for a custom absolute path.')) . '</p></div>';
        } else {
            $inHouseLabelReplacements = [
                'Products (In-house)' => translate('Products'),
                'Product (In-house)' => translate('Product'),
                'Manage In-house Products' => translate('Manage Products'),
                'Your In-house Products' => translate('Your Products'),
                'In-house Products' => translate('Products'),
                'Inhouse Products' => translate('Products'),
                'INHOUSE PRODUCTS' => function_exists('mb_strtoupper') ? mb_strtoupper(translate('Products'), 'UTF-8') : strtoupper(translate('Products')),
                'In-house Product' => translate('Product'),
                'Inhouse Product' => translate('Product'),
                'INHOUSE PRODUCT' => function_exists('mb_strtoupper') ? mb_strtoupper(translate('Product'), 'UTF-8') : strtoupper(translate('Product')),
                'In-house products' => translate('Products'),
                'Inhouse products' => translate('Products'),
                'in-house products' => translate('Products'),
                'In-house product' => translate('Product'),
                'Inhouse product' => translate('Product'),
                'in-house product' => translate('Product'),
                'Add In-house Product' => translate('Add New product'),
                'Add Inhouse Product' => translate('Add New product'),
            ];

            uksort($inHouseLabelReplacements, static function ($a, $b) {
                return strlen($b) <=> strlen($a);
            });

            foreach ($inHouseLabelReplacements as $from => $to) {
                if ($from !== '') {
                    $v2Html = str_ireplace($from, $to, $v2Html);
                }
            }
        }

        $brands = \App\Models\Brand::select('id', 'name')->orderBy('name')->get();
        $colors = \App\Models\Color::select('name', 'code')->orderBy('name')->get();
        $attributes = \App\Models\Attribute::all();
        $flashDeals = \App\Models\FlashDeal::where('status', 1)->select('id', 'title')->orderBy('title')->get();
        $linkedProducts = \App\Models\Product::query()
            ->where('auction_product', 0)
            ->where('wholesale_product', 0)
            ->where('digital', 0)
            ->select('id', 'name')
            ->orderBy('name')
            ->limit(1200)
            ->get();
        $warrantyOptions = class_exists(\App\Models\Warranty::class)
            ? \App\Models\Warranty::query()->select('id', 'text')->orderBy('id')->get()
            : collect();
        $categoryNameToId = [];
        $flatCategories = [];
        $flatten = function ($items, $depth = 0) use (&$flatten, &$categoryNameToId, &$flatCategories) {
            foreach ($items as $item) {
                $name = trim($item->getTranslation('name'));
                if ($name !== '') {
                    $categoryNameToId[mb_strtolower($name)] = $item->id;
                    $flatCategories[] = ['id' => $item->id, 'name' => $name, 'depth' => (int) $depth];
                }
                if ($item->childrenCategories && $item->childrenCategories->count()) {
                    $flatten($item->childrenCategories, $depth + 1);
                }
            }
        };
        $flatten($categories ?? collect());
        $attributeNameToId = [];
        foreach ($attributes as $attr) {
            $attributeNameToId[mb_strtolower(trim($attr->getTranslation('name')))] = $attr->id;
        }
    @endphp

    {!! $v2Html !!}
    <style>
        /* GLOBAL MEDIA PICKER SYSTEM (single approved uploader UI for whole product page) */
        body.v2-product-create-page {
            --v2-media-box-radius: 8px;
            --v2-media-box-border: #d9e2ec;
            --v2-media-box-bg: #fcfdff;
            --v2-media-box-hover-border: #b6dfff;
            --v2-media-box-hover-bg: #f8fbff;
            --v2-media-title-size: 13px;
            --v2-media-title-color: #4b5d72;
            --v2-media-sub-size: 11px;
            --v2-media-sub-color: #97a6b8;
            --v2-media-thumb-radius: 6px;
            --v2-media-thumb-border: #e6edf5;
            --v2-media-thumb-bg: #f8fafc;
            --v2-media-remove-bg: rgba(15, 23, 42, 0.62);
            --v2-media-remove-bg-hover: #ef4444;
        }
        body.v2-product-create-page .idrop,
        body.v2-product-create-page .go,
        body.v2-product-create-page .v2-video-upload-card,
        body.v2-product-create-page .v2-pdf-box .v2-pdf-browse-btn {
            border-radius: var(--v2-media-box-radius) !important;
            border: 1px solid var(--v2-media-box-border) !important;
            background: var(--v2-media-box-bg) !important;
            box-shadow: none !important;
            transition: all .2s ease !important;
        }
        body.v2-product-create-page .v2-video-upload-card {
            min-height: 66px;
            padding: 10px 12px;
            margin: 0;
            width: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            gap: 2px;
            line-height: 1.2;
        }
        body.v2-product-create-page .idrop:hover,
        body.v2-product-create-page .go:hover,
        body.v2-product-create-page .v2-video-upload-card:hover,
        body.v2-product-create-page .v2-pdf-box .v2-pdf-browse-btn:hover {
            border-color: var(--v2-media-box-hover-border) !important;
            background: var(--v2-media-box-hover-bg) !important;
        }
        body.v2-product-create-page .idrop span,
        body.v2-product-create-page .go span,
        body.v2-product-create-page .v2-video-upload-card {
            font-size: var(--v2-media-title-size) !important;
            color: var(--v2-media-title-color) !important;
        }
        body.v2-product-create-page .idrop small,
        body.v2-product-create-page .go small,
        body.v2-product-create-page .v2-video-card-meta {
            font-size: var(--v2-media-sub-size) !important;
            color: var(--v2-media-sub-color) !important;
        }
        body.v2-product-create-page .v2-video-upload-card > small {
            font-size: var(--v2-media-sub-size) !important;
            color: var(--v2-media-sub-color) !important;
            display: block;
            margin: 0;
        }
        body.v2-product-create-page .v2-media-thumb-wrap,
        body.v2-product-create-page .v2-media-slot-wrap,
        body.v2-product-create-page .v2-video-thumb-preview,
        body.v2-product-create-page .v2-video-file-preview,
        body.v2-product-create-page .v2-seo-og-preview,
        body.v2-product-create-page .v2-pdf-selected {
            position: relative;
            width: 100%;
            border-radius: var(--v2-media-thumb-radius);
            overflow: hidden;
            background: var(--v2-media-thumb-bg);
            border: 1px solid var(--v2-media-thumb-border) !important;
            box-shadow: none !important;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        body.v2-product-create-page .v2-video-thumb-preview,
        body.v2-product-create-page .v2-video-file-preview {
            min-height: 70px;
            height: 100%;
        }
        body.v2-product-create-page .v2-pdf-selected {
            margin-top: 10px;
            font-size: 12px;
            color: #334155;
            padding: 8px 34px 8px 10px;
            min-height: 36px;
            display: flex;
            align-items: center;
        }
        body.v2-product-create-page .v2-media-thumb-wrap img,
        body.v2-product-create-page .v2-media-slot-wrap img,
        body.v2-product-create-page .v2-video-thumb-preview img,
        body.v2-product-create-page .v2-video-file-preview video,
        body.v2-product-create-page .v2-seo-og-preview img {
            width: 100%;
            height: auto !important;
            max-height: 320px;
            object-fit: contain;
            border-radius: 8px;
            display: block;
        }
        body.v2-product-create-page .v2-remove-thumb,
        body.v2-product-create-page .v2-remove-gallery,
        body.v2-product-create-page .v2-video-mini-remove,
        body.v2-product-create-page .v2-pdf-remove,
        body.v2-product-create-page .v2-seo-og-remove {
            width: 20px;
            height: 20px;
            position: absolute;
            top: 5px;
            right: 5px;
            border: none;
            border-radius: 999px;
            background: var(--v2-media-remove-bg) !important;
            color: #fff !important;
            font-size: 14px;
            line-height: 1;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            text-decoration: none !important;
            transition: transform .15s ease, background .15s ease;
        }
        body.v2-product-create-page .v2-remove-thumb:hover,
        body.v2-product-create-page .v2-remove-gallery:hover,
        body.v2-product-create-page .v2-video-mini-remove:hover,
        body.v2-product-create-page .v2-pdf-remove:hover,
        body.v2-product-create-page .v2-seo-og-remove:hover {
            transform: scale(1.06);
            background: var(--v2-media-remove-bg-hover) !important;
        }
        body.v2-product-create-page .gg {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 7px;
            margin-top: 8px;
        }
        body.v2-product-create-page .gg .gs {
            border-radius: 6px;
            overflow: hidden;
            border: 1px solid #e6edf5 !important;
            background: #fff;
            transition: all .18s ease;
        }
        body.v2-product-create-page .gg .gs:hover {
            border-color: #b6dfff !important;
            background: #f8fbff;
        }
        body.v2-product-create-page .gg .gs[draggable="true"] { cursor: grab; }
        body.v2-product-create-page .gg .gs.v2-dragging { opacity: .45; cursor: grabbing; }
        body.v2-product-create-page .v2-empty-slot {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 100%;
            color: #a9b8c8;
        }
        body.v2-product-create-page .sbft {
            font-size: 12px;
            color: #95a4b6;
            margin-top: 10px;
        }
        body.v2-product-create-page .v2-video-links-compact {
            min-height: 40px !important;
            height: 40px !important;
            padding: 8px 10px !important;
            font-size: 13px !important;
            resize: vertical;
        }
        body.v2-product-create-page .v2-video-upload-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 8px;
            margin-top: 8px;
            padding: 2px 2px 4px;
            width: 100%;
        }
        body.v2-product-create-page .v2-video-box .sbbd,
        body.v2-product-create-page .v2-video-box .mbd {
            padding-bottom: 20px !important;
        }
        body.v2-product-create-page .v2-video-box .v2-video-upload-grid {
            margin-bottom: 10px !important;
            padding-bottom: 8px !important;
        }
        body.v2-product-create-page .v2-video-upload-card {
            font-weight: 500;
            color: var(--v2-media-title-color) !important;
            width: calc(100% - 20px) !important;
            max-width: calc(100% - 20px) !important;
            margin-left: auto !important;
            margin-right: auto !important;
            transform: none !important;
            box-shadow: none !important;
        }
        body.v2-product-create-page .v2-video-upload-card:hover,
        body.v2-product-create-page .v2-video-upload-card:focus {
            border-color: var(--v2-media-box-hover-border) !important;
            background: var(--v2-media-box-hover-bg) !important;
            color: var(--v2-media-title-color) !important;
            box-shadow: none !important;
            outline: none !important;
        }
        body.v2-product-create-page .v2-pdf-box .v2-pdf-upload-row {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 8px;
            align-items: center;
        }
        body.v2-product-create-page .v2-pdf-box .v2-pdf-file-field {
            height: 38px;
            border-radius: 8px;
            border: 1px solid #d9e2ec !important;
            background: #fcfdff;
            color: #4b5d72;
            padding: 0 12px;
            display: flex;
            align-items: center;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        body.v2-product-create-page .v2-video-card-meta {
            margin-top: 5px;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 8px;
        }
        body.v2-product-create-page .v2-video-file-count {
            position: absolute;
            left: 6px;
            top: 6px;
            background: rgba(15, 23, 42, 0.72);
            color: #fff;
            font-size: 10px;
            padding: 2px 6px;
            border-radius: 999px;
            z-index: 2;
        }
        /* Brand + tags panel polish */
        .v2-brand-box select {
            height: 38px;
            color: #334155;
            width: 100%;
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            padding: 0 34px 0 12px;
            background-image: linear-gradient(45deg, transparent 50%, #64748b 50%), linear-gradient(135deg, #64748b 50%, transparent 50%);
            background-position: calc(100% - 16px) calc(50% - 3px), calc(100% - 11px) calc(50% - 3px);
            background-size: 5px 5px, 5px 5px;
            background-repeat: no-repeat;
        }
        .v2-brand-box a {
            font-size: 12px;
            color: #0a66c2;
            text-decoration: none;
        }
        .v2-brand-box a:hover {
            text-decoration: underline;
        }
        .v2-brand-box .bootstrap-select {
            width: 100% !important;
            max-width: calc(100% - 20px) !important;
            margin-left: auto !important;
            margin-right: auto !important;
        }
        .v2-brand-box .bootstrap-select > .dropdown-toggle {
            height: 40px;
            color: #334155 !important;
            padding: 0 36px 0 12px;
            border: 1px solid #d7e2ee !important;
            border-radius: 8px !important;
            background: #fff !important;
            box-shadow: none !important;
        }
        .v2-brand-box .bootstrap-select .filter-option {
            display: flex;
            align-items: center;
            height: 100%;
        }
        .v2-brand-box .bootstrap-select .filter-option-inner-inner {
            color: #334155 !important;
            font-size: 15px;
        }
        .v2-brand-box .bootstrap-select .dropdown-menu {
            border: 1px solid #dbe5ef;
            border-radius: 8px;
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.08);
        }
        .v2-brand-box .sbbd,
        .v2-brand-box .mbd {
            padding: 10px 12px !important;
        }
        .v2-brand-box .bootstrap-select {
            margin-top: 8px !important;
            margin-bottom: 8px !important;
        }
        .v2-brand-box .sbbd > div,
        .v2-brand-box .mbd > div {
            border: 0 !important;
            background: transparent !important;
            padding: 0 !important;
            box-shadow: none !important;
        }
        .v2-brand-box .sbbd .f,
        .v2-brand-box .sbbd .g,
        .v2-brand-box .sbbd .in,
        .v2-brand-box .mbd .f,
        .v2-brand-box .mbd .g,
        .v2-brand-box .mbd .in {
            border: 0 !important;
            background: transparent !important;
            padding: 0 !important;
            margin: 0 !important;
            box-shadow: none !important;
        }
        .v2-brand-box .sbbd select,
        .v2-brand-box .sbbd .bootstrap-select,
        .v2-brand-box .mbd select,
        .v2-brand-box .mbd .bootstrap-select {
            margin: 0 !important;
        }
        .v2-brand-box .sbbd .bootstrap-select > .dropdown-toggle,
        .v2-brand-box .mbd .bootstrap-select > .dropdown-toggle {
            height: 40px;
            border-radius: 8px;
            border: 1px solid #d7e2ee !important;
            background: #fff !important;
            box-shadow: none !important;
        }
        .v2-tags-box .tg {
            gap: 6px;
            align-items: center;
        }
        .v2-tags-box .tg input {
            height: 40px;
            border-radius: 8px;
            border: 1px solid #d7e2ee;
            padding: 0 10px;
            box-shadow: none !important;
            background: #fff;
        }
        .v2-tags-box .tg button {
            height: 40px;
            min-width: 42px;
            border-radius: 8px;
            border: 1px solid #d7e2ee;
            background: #fff;
            color: #334155;
            font-size: 13px;
            font-weight: 600;
            box-shadow: none !important;
        }
        .v2-tags-box .tg button:hover,
        .v2-tags-box .tg button:focus {
            border-color: #b6dfff !important;
            background: #f8fbff !important;
            color: #0f4c81 !important;
            outline: none;
        }
        .v2-tags-box .tg input:focus {
            border-color: #bcdfff !important;
            box-shadow: 0 0 0 2px rgba(188, 223, 255, 0.35) !important;
            outline: none;
        }
        .v2-tags-box .tags {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin-top: 2px;
            margin-bottom: 8px;
            width: 100%;
            position: static !important;
            float: none !important;
            clear: both;
            transform: translateY(-4px);
            padding-left: 6px;
        }
        .v2-tags-box .pill {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            border: 1px solid #d7e2ee;
            background: #f8fbff;
            color: #334155;
            border-radius: 6px;
            font-size: 12px;
            line-height: 1;
            padding: 6px 8px;
        }
        .v2-tags-box .pill .x {
            cursor: pointer;
            color: #718096;
            font-weight: 700;
        }
        .v2-tags-box .pill .x:hover {
            color: #b42318;
        }
        /* SEO in V2 follows original Add Product SEO structure (simple + compact). */
        /* Keep summernote frame clean and flush */
        #v2_short_desc_editor + .note-editor.note-frame,
        #v2_main_editor + .note-editor.note-frame {
            margin-top: 0 !important;
            border-radius: 8px;
            overflow: hidden;
            border-color: #d7e2ee;
        }
        /* Category box polish */
        .v2-cat-box .cl {
            min-height: 220px;
            max-height: 300px;
            overflow: auto;
            border: 1px solid #dbe5ef;
            border-radius: 8px;
            background: #fff;
            padding: 8px;
            scrollbar-width: thin;
            scrollbar-color: #c7d6e8 #f7fbff;
        }
        .v2-cat-box .cl::-webkit-scrollbar {
            width: 8px;
        }
        .v2-cat-box .cl::-webkit-scrollbar-track {
            background: #f7fbff;
            border-radius: 999px;
        }
        .v2-cat-box .cl::-webkit-scrollbar-thumb {
            background: linear-gradient(180deg, #d2deed 0%, #c2d2e4 100%);
            border-radius: 999px;
            border: 2px solid #f7fbff;
        }
        .v2-cat-box .cl::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(180deg, #bccde1 0%, #adc2da 100%);
        }
        .v2-cat-box .ci {
            display: flex;
            align-items: center;
            gap: 8px;
            margin: 0 0 2px;
            padding: 3px 8px;
            border-radius: 6px;
            cursor: pointer;
            color: #334155;
        }
        .v2-cat-box .ci .cat-label {
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .v2-cat-box .ci[data-depth="1"] { padding-left: 20px; }
        .v2-cat-box .ci[data-depth="2"] { padding-left: 32px; }
        .v2-cat-box .ci[data-depth="3"] { padding-left: 44px; }
        .v2-cat-box .ci[data-depth="4"] { padding-left: 56px; }
        .v2-cat-box .ci[data-depth="5"] { padding-left: 68px; }
        .v2-cat-box .ci[data-depth]:not([data-depth="0"]) .cat-label::before { content: none; }
        .v2-cat-box .ci:hover {
            background: #f5f9ff;
        }
        .v2-cat-box .cat-tabs a,
        .v2-cat-box .cat-tabs button {
            cursor: pointer;
        }
        .v2-cat-box .cat-tabs {
            display: flex;
            align-items: center;
            gap: 4px;
            border-bottom: 1px solid #e5edf5;
            padding-bottom: 4px;
            margin-bottom: 8px;
        }
        .v2-cat-box .cat-tabs a,
        .v2-cat-box .cat-tabs button {
            border: 0;
            background: transparent;
            color: #64748b;
            font-size: 14px;
            font-weight: 600;
            line-height: 1.2;
            padding: 4px 8px 6px;
            transform: translateY(-1px);
            border-radius: 8px 8px 0 0;
            text-decoration: none;
            transition: color .16s ease, background-color .16s ease;
        }
        .v2-cat-box .cat-tabs a.active,
        .v2-cat-box .cat-tabs button.active {
            color: #0b67b3;
            background: #f5f9ff;
            box-shadow: inset 0 -2px 0 #0b67b3;
        }
        .v2-cat-box .cat-tabs a:hover,
        .v2-cat-box .cat-tabs button:hover {
            color: #0f4c81;
            background: #f7fbff;
        }
        .v2-cat-box .ci input {
            margin: 0;
        }
        .v2-cat-box input[placeholder*="Search categories"] {
            height: 36px;
            border-radius: 8px;
            border-color: #d7e2ee;
        }
        /* Product data tabs: premium clean nav + panel rhythm */
        .woo {
            border: 1px solid #dbe5ef !important;
            border-radius: 10px !important;
            overflow: hidden;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.03);
        }
        .woo .woo-hd {
            padding: 10px 14px;
            background: #f8fbff !important;
            border-bottom: 1px solid #dbe5ef !important;
        }
        .woo .woo-hd h3 {
            font-size: 14px;
            font-weight: 600;
            color: #0f172a;
            letter-spacing: .1px;
        }
        .woo .woo-body {
            grid-template-columns: 170px minmax(0, 1fr);
            background: #fff;
        }
        .woo .wtabs {
            background: #f8fafc !important;
            border-right: 1px solid #dbe5ef !important;
            padding: 8px 0;
        }
        .woo .wt {
            position: relative;
            display: flex;
            align-items: center;
            gap: 8px;
            min-height: 42px;
            margin: 2px 8px;
            padding: 9px 11px 9px 12px;
            font-size: 13px;
            line-height: 1.25;
            color: #475569 !important;
            border: 1px solid transparent !important;
            border-left: 2px solid transparent !important;
            border-radius: 8px;
            background: transparent !important;
            transition: all .16s ease;
        }
        .woo .wt:hover {
            background: #f5f9ff !important;
            color: #0f4c81 !important;
            border-color: #d6eaff !important;
        }
        .woo .wt.on {
            background: #eff7ff !important;
            color: #0b67b3 !important;
            border-color: #c9e5ff !important;
            border-left-color: #009ef7 !important;
            font-weight: 600;
        }
        .woo .wt i {
            width: 17px;
            font-size: 15px;
            color: inherit !important;
            opacity: .92;
        }
        .woo .wpanel {
            padding: 16px 18px;
            background: #fff;
        }
        .woo .wpanel .sec {
            letter-spacing: .07em;
            font-size: 11px;
            margin-bottom: 10px;
        }
        .woo .wpanel .f {
            margin-bottom: 12px;
        }
        /* Final UX pass for Product data area */
        body.v2-product-create-page {
            /* Product Data only: vertical tab strip (Pricing, Inventory…). Content column stays fluid. */
            --v2-product-data-tab-rail-width: 192px;
            /* One column width for title + Product description/data/short desc/SEO/PDF stacks */
            --v2-main-content-blocks-max-width: 1250px;
            /* Small left breathing space for major main-column sections */
            --v2-main-sections-left-gap: clamp(8px, 1vw, 12px);
            /* Right column (Publish, categories, …): lg+ grid track — wide enough to read, not heavy */
            --v2-sidebar-metabox-column-width: 300px;
            --v2-split-column-gap: clamp(18px, 2.2vw, 28px);
            /* Small, reusable vertical rhythm between major stacked sections */
            --v2-section-stack-gap: 12px;
            --v2-section-hd-bg: linear-gradient(180deg, #fbfdff 0%, #f6faff 100%);
            --v2-section-hd-border: #dde7f1;
            --v2-section-hd-title: #0f172a;
            --v2-section-hd-height: 46px;
            --v2-section-hd-pad-y: 11px;
            --v2-section-hd-pad-x: 16px;
            --v2-section-hd-icon-border: #d6e3f0;
            --v2-section-hd-icon-color: #5f738a;
            --v2-section-hd-icon-bg: #ffffff;
            --v2-section-hd-icon-hover-bg: #f5f9ff;
            --v2-section-hd-icon-hover-color: #0f4c81;
            --v2-section-hd-icon-hover-border: #bcdfff;
        }
        body.v2-product-create-page .woo {
            border: 1px solid #d7e3ef !important;
            border-radius: 12px !important;
            box-shadow: 0 8px 24px rgba(15, 23, 42, 0.04);
            overflow: hidden;
            background: #fff !important;
        }
        body.v2-product-create-page .woo .woo-hd {
            background: var(--v2-section-hd-bg) !important;
            border-bottom: 1px solid var(--v2-section-hd-border) !important;
            min-height: var(--v2-section-hd-height);
            padding: var(--v2-section-hd-pad-y) var(--v2-section-hd-pad-x);
            display: flex;
            align-items: center;
            justify-content: flex-start;
            position: relative;
            cursor: pointer;
            user-select: none;
            padding-right: 42px !important;
        }
        body.v2-product-create-page .woo .woo-hd h3 {
            font-size: 14px;
            font-weight: 700;
            color: var(--v2-section-hd-title);
            margin: 0;
            text-align: left;
            width: auto;
        }
        /* Globalize Product-data header design for all main section headers */
        body.v2-product-create-page .mbox > .mhd,
        body.v2-product-create-page .mbox .mhd:first-child {
            background: var(--v2-section-hd-bg) !important;
            border-bottom: 1px solid var(--v2-section-hd-border) !important;
            min-height: var(--v2-section-hd-height);
            padding: var(--v2-section-hd-pad-y) var(--v2-section-hd-pad-x) !important;
            display: flex;
            align-items: center;
            margin: 0 !important;
        }
        body.v2-product-create-page .mbox > .mhd h2,
        body.v2-product-create-page .mbox > .mhd h3,
        body.v2-product-create-page .mbox > .mhd h4,
        body.v2-product-create-page .mbox .mhd:first-child h2,
        body.v2-product-create-page .mbox .mhd:first-child h3,
        body.v2-product-create-page .mbox .mhd:first-child h4 {
            margin: 0 !important;
            font-size: 14px !important;
            font-weight: 700 !important;
            line-height: 1.2 !important;
            color: var(--v2-section-hd-title) !important;
        }
        body.v2-product-create-page .woo .woo-hd .v2-woo-toggle-icon {
            width: 22px;
            height: 22px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid var(--v2-section-hd-icon-border);
            border-radius: 999px;
            color: var(--v2-section-hd-icon-color);
            background: var(--v2-section-hd-icon-bg);
            line-height: 1;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.06);
            transition: transform .2s ease, background-color .2s ease, color .2s ease, border-color .2s ease, box-shadow .2s ease;
            animation: none;
            position: absolute;
            right: 12px;
            top: 50%;
            margin-top: -11px;
        }
        body.v2-product-create-page .woo .woo-hd .v2-woo-toggle-icon svg {
            width: 12px;
            height: 12px;
            display: block;
        }
        body.v2-product-create-page .woo .woo-hd .v2-woo-toggle-icon svg path {
            stroke: currentColor;
            stroke-width: 2.2;
            stroke-linecap: round;
            stroke-linejoin: round;
            fill: none;
        }
        body.v2-product-create-page .woo .woo-hd:hover .v2-woo-toggle-icon {
            background: var(--v2-section-hd-icon-hover-bg);
            color: var(--v2-section-hd-icon-hover-color);
            border-color: var(--v2-section-hd-icon-hover-border);
            box-shadow: 0 2px 6px rgba(15, 76, 129, 0.12);
        }
        body.v2-product-create-page .woo.is-collapsed .woo-hd .v2-woo-toggle-icon {
            transform: rotate(-90deg);
        }
        body.v2-product-create-page .woo.is-collapsed .woo-hd:hover .v2-woo-toggle-icon,
        body.v2-product-create-page .woo.is-collapsed .woo-hd .v2-woo-toggle-icon { animation: none; }
        body.v2-product-create-page .woo.is-collapsed .woo-body {
            display: none !important;
        }
        body.v2-product-create-page .woo > .woo-body {
            display: block;
            background: #fff;
        }
        /* Keep Product data's original 2-column internals only for that section */
        body.v2-product-create-page .woo.v2-layout-tabs > .woo-body {
            display: grid;
            grid-template-columns: var(--v2-product-data-tab-rail-width) minmax(0, 1fr);
        }
        /* Product description final lock is defined at file end (v2-description-absolute-lock). */
        body.v2-product-create-page .woo.v2-layout-tabs .wtabs {
            background: #f7fafd !important;
            border-right: 1px solid #dde7f1 !important;
            padding: 8px 6px;
        }
        body.v2-product-create-page .woo.v2-layout-tabs .wt {
            margin: 2px 0;
            padding: 9px 10px;
            min-height: 40px;
            border-radius: 8px;
            border: 1px solid transparent !important;
            border-left: 3px solid transparent !important;
            color: #475569 !important;
            font-weight: 500;
            letter-spacing: .01em;
            transition: all .18s ease;
            justify-content: flex-start;
            text-align: left;
        }
        body.v2-product-create-page .woo.v2-layout-tabs .wt:hover {
            background: #f5f9ff !important;
            color: #0f4c81 !important;
            border-color: #d7e8fb !important;
            transform: translateX(1px);
        }
        body.v2-product-create-page .woo.v2-layout-tabs .wt.on {
            background: #edf6ff !important;
            border-color: #cae4ff !important;
            border-left-color: #009ef7 !important;
            color: #0b67b3 !important;
            font-weight: 700;
            box-shadow: inset 0 0 0 1px rgba(0, 158, 247, 0.06);
        }
        body.v2-product-create-page .woo.v2-layout-tabs .wt i {
            display: none !important;
        }
        /* Shared max-width: matches Product name strip + description + Product data + short desc + SEO + PDF */
        html body.v2-product-create-page .v2-product-title-block-root,
        html body.v2-product-create-page .v2-main-content-block {
            width: 100% !important;
            max-width: min(calc(var(--v2-main-content-blocks-max-width) - var(--v2-main-sections-left-gap)), 100%) !important;
            margin-left: var(--v2-main-sections-left-gap) !important;
            margin-right: auto !important;
            box-sizing: border-box;
        }
        /* Global major section rhythm (main column wrappers only) */
        html body.v2-product-create-page .v2-major-flow-section {
            margin-top: 0 !important;
        }
        html body.v2-product-create-page .v2-major-flow-section.v2-major-flow-spaced {
            margin-top: var(--v2-section-stack-gap) !important;
        }
        html body.v2-product-create-page .cibato-main-content {
            scrollbar-gutter: stable;
            max-width: 100%;
            box-sizing: border-box;
            overflow-x: hidden;
        }
        html body.v2-product-create-page .cibato-main-content > .px-15px.px-lg-25px {
            max-width: 100%;
            box-sizing: border-box;
            overflow-x: hidden;
        }
        @media (min-width: 992px) {
            /*
             * Split row: JS-tagged OR any .row under main that contains Publish (.sb).
             * (Template may omit .row on the tagged node — do not require .row in selector.)
             */
            html body.v2-product-create-page .v2-product-split-layout-row,
            html body.v2-product-create-page .cibato-main-content .row:has(.sb) {
                display: grid !important;
                grid-template-columns: minmax(0, 1fr) min(var(--v2-sidebar-metabox-column-width), 100%);
                column-gap: var(--v2-split-column-gap);
                row-gap: 0;
                align-items: flex-start;
                width: 100% !important;
                max-width: 100% !important;
                box-sizing: border-box;
                margin-left: 0 !important;
                margin-right: 0 !important;
                overflow-x: hidden;
                flex-wrap: unset !important;
            }
            html body.v2-product-create-page .v2-product-split-layout-row > [class*="col-"],
            html body.v2-product-create-page .cibato-main-content .row:has(.sb) > [class*="col-"] {
                flex: unset !important;
                flex-grow: unset !important;
                flex-shrink: unset !important;
                flex-basis: unset !important;
                max-width: none !important;
                width: auto !important;
            }
            html body.v2-product-create-page .v2-product-split-layout-row > .v2-main-editor-flex-column,
            html body.v2-product-create-page .cibato-main-content .row:has(.sb) > [class*="col-"]:not(:has(.sb)) {
                grid-column: 1 !important;
                grid-row: 1 !important;
                min-width: 0 !important;
                overflow-x: auto !important;
                box-sizing: border-box;
            }
            html body.v2-product-create-page .v2-product-split-layout-row > .v2-sidebar-metabox-column,
            html body.v2-product-create-page .cibato-main-content .row:has(.sb) > [class*="col-"]:has(.sb) {
                grid-column: 2 !important;
                grid-row: 1 !important;
                width: 100% !important;
                min-width: 0 !important;
                max-width: min(var(--v2-sidebar-metabox-column-width), 100%) !important;
                overflow-x: hidden !important;
                box-sizing: border-box !important;
                padding-inline-end: 6px !important;
                justify-self: stretch;
                align-self: start;
                display: flex;
                flex-direction: column;
                gap: 14px;
            }
            html body.v2-product-create-page .v2-sidebar-metabox-column .sb {
                width: 100%;
                max-width: 100%;
                box-sizing: border-box;
            }
            html body.v2-product-create-page .v2-sidebar-metabox-shell.sb {
                width: min(var(--v2-sidebar-metabox-column-width), 100%) !important;
                max-width: min(var(--v2-sidebar-metabox-column-width), 100%) !important;
                margin-left: 0 !important;
                margin-right: auto !important;
                box-sizing: border-box;
            }
        }
        @media (max-width: 991.98px) {
            html body.v2-product-create-page .v2-product-split-layout-row,
            html body.v2-product-create-page .cibato-main-content .row:has(.sb) {
                display: flex !important;
                flex-wrap: wrap !important;
                grid-template-columns: none !important;
                column-gap: 0 !important;
                gap: 0;
                overflow-x: visible;
            }
            html body.v2-product-create-page .v2-product-split-layout-row > .v2-main-editor-flex-column,
            html body.v2-product-create-page .v2-product-split-layout-row > .v2-sidebar-metabox-column,
            html body.v2-product-create-page .cibato-main-content .row:has(.sb) > [class*="col-"] {
                grid-column: unset !important;
                grid-row: unset !important;
            }
            html body.v2-product-create-page .v2-sidebar-metabox-column,
            html body.v2-product-create-page .v2-main-editor-flex-column {
                flex-basis: auto !important;
                max-width: 100% !important;
                width: 100% !important;
                min-width: 0 !important;
                padding-inline-end: 0 !important;
                overflow-x: visible;
            }
            html body.v2-product-create-page .v2-sidebar-metabox-shell.sb {
                width: 100% !important;
                max-width: 100% !important;
            }
        }
        body.v2-product-create-page .woo.v2-layout-tabs .wpanel {
            padding: 18px 20px;
            background: #fff;
        }
        body.v2-product-create-page .woo.v2-layout-tabs .wpanel .sec {
            font-size: 10px;
            font-weight: 700;
            color: #64748b;
            letter-spacing: .09em;
            margin-bottom: 11px;
            text-transform: uppercase;
        }
        body.v2-product-create-page .woo.v2-layout-tabs .wpanel .lbl {
            font-size: 12px;
            color: #334155;
            margin-bottom: 6px;
            font-weight: 600;
        }
        /* Control border system is unified by the global master block at file end. */
        body.v2-product-create-page .woo .wpanel .hint {
            font-size: 11px;
            color: #64748b;
            margin-top: 4px;
        }
        body.v2-product-create-page .woo .wpanel table {
            border-color: #dbe5ef !important;
        }
        body.v2-product-create-page .woo .wpanel table th {
            background: #f8fbff !important;
            color: #334155;
            font-weight: 600;
        }
        body.v2-product-create-page .woo .wpanel table td {
            background: #fff;
        }
        /* Shipping panel visual polish */
        body.v2-product-create-page #p-shipping .notice {
            background: #fff8e8;
            border-left: 4px solid #e0b44c;
            border-radius: 8px;
            padding: 9px 12px;
            color: #4b5563;
            font-size: 12px;
            margin-bottom: 12px;
        }
        body.v2-product-create-page #p-shipping .info-notice {
            background: #eef6ff;
            border-left: 4px solid #66aee8;
            border-radius: 8px;
            padding: 9px 12px;
            color: #1e4f7a;
            font-size: 12px;
            margin-bottom: 10px;
        }
        body.v2-product-create-page #p-shipping .r2 {
            gap: 12px;
        }
        body.v2-product-create-page #p-shipping .co {
            display: flex;
            align-items: center;
            gap: 8px;
            min-height: 28px;
            color: #334155;
            font-size: 13px;
            margin-bottom: 4px;
        }
        body.v2-product-create-page #p-shipping .co input[type="checkbox"] {
            width: 15px;
            height: 15px;
            accent-color: #009ef7;
            cursor: pointer;
        }
        body.v2-product-create-page #p-shipping .gbtn {
            border: 1px solid #cfdceb;
            border-radius: 8px;
            background: #fff;
            color: #334155;
            font-size: 12px;
            font-weight: 600;
            padding: 7px 12px;
            transition: all .16s ease;
        }
        body.v2-product-create-page #p-shipping .gbtn:hover {
            background: #f5f9ff;
            border-color: #bcdfff;
            color: #0f4c81;
        }
        body.v2-product-create-page #p-shipping .v2-shipping-notes-list {
            margin-top: 10px !important;
            border: 1px solid #dbe5ef;
            background: #fbfdff;
            border-radius: 8px;
            padding: 8px 10px;
        }
        body.v2-product-create-page #p-shipping .v2-note-item {
            margin-top: 0 !important;
            padding: 5px 0;
            border-bottom: 1px dashed #e2e8f0;
        }
        body.v2-product-create-page #p-shipping .v2-note-item:last-child {
            border-bottom: 0;
        }
        body.v2-product-create-page #p-shipping .v2-note-item .v2-note-remove:hover {
            color: #ef4444 !important;
        }
        /* Linked products panel enhancements */
        body.v2-product-create-page #p-linked .notice,
        body.v2-product-create-page #p-linked .info-notice,
        body.v2-product-create-page #p-linked .alert {
            background: #eef6ff !important;
            border-left: 4px solid #66aee8 !important;
            border-radius: 8px !important;
            padding: 9px 12px !important;
            color: #1e4f7a !important;
            font-size: 12px !important;
            margin-bottom: 10px !important;
            box-shadow: none !important;
        }
        body.v2-product-create-page #p-linked .v2-linked-select .bootstrap-select > .dropdown-toggle {
            min-height: 40px;
            border: 1px solid #d7e2ee !important;
            border-radius: 8px;
            background: #fff !important;
        }
        body.v2-product-create-page #p-linked .v2-linked-meta {
            margin-top: 6px;
            font-size: 11px;
            color: #64748b;
        }
        /* Warranty & COD panel polish */
        body.v2-product-create-page #p-warranty .tr {
            padding: 10px 0;
            border-bottom: 1px solid #e2ebf3;
        }
        body.v2-product-create-page #p-warranty .tl {
            font-size: 14px;
            font-weight: 600;
            color: #0f172a;
        }
        body.v2-product-create-page #p-warranty .ts {
            font-size: 12px;
            color: #64748b;
        }
        body.v2-product-create-page #p-warranty .co {
            display: flex;
            align-items: center;
            gap: 8px;
            margin: 8px 0 6px;
            color: #334155;
            font-size: 13px;
        }
        body.v2-product-create-page #p-warranty .co input[type="checkbox"] {
            width: 15px;
            height: 15px;
            accent-color: #009ef7;
        }
        body.v2-product-create-page #p-warranty .gbtn {
            border: 1px solid #cfdceb;
            border-radius: 8px;
            background: #fff;
            color: #334155;
            font-size: 12px;
            font-weight: 600;
            padding: 7px 12px;
            transition: all .16s ease;
        }
        body.v2-product-create-page #p-warranty .gbtn:hover {
            background: #f5f9ff;
            border-color: #bcdfff;
            color: #0f4c81;
        }
        body.v2-product-create-page #p-warranty .v2-warranty-notes-list,
        body.v2-product-create-page #p-warranty .v2-cod-notes-list {
            margin-top: 8px;
            border: 1px solid #dbe5ef;
            background: #fbfdff;
            border-radius: 8px;
            padding: 8px 10px;
        }
        body.v2-product-create-page #p-warranty .v2-note-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            padding: 5px 0;
            border-bottom: 1px dashed #e2e8f0;
            font-size: 12px;
            color: #334155;
        }
        body.v2-product-create-page #p-warranty .v2-note-item:last-child {
            border-bottom: 0;
        }
        body.v2-product-create-page #p-warranty .v2-note-remove {
            border: 0;
            background: transparent;
            color: #94a3b8;
            cursor: pointer;
            font-size: 14px;
            line-height: 1;
        }
        body.v2-product-create-page #p-warranty .v2-note-remove:hover {
            color: #ef4444;
        }
        body.v2-product-create-page #p-warranty .v2-note-edit {
            flex: 1;
            min-height: 28px;
            border: 1px solid transparent;
            background: transparent;
            color: #334155;
            font-size: 12px;
            border-radius: 6px;
            padding: 4px 6px;
        }
        body.v2-product-create-page #p-warranty .v2-note-edit:focus {
            outline: none;
            border-color: #bcdfff;
            background: #ffffff;
            box-shadow: 0 0 0 2px rgba(188, 223, 255, 0.35);
        }
        body.v2-product-create-page #p-warranty .v2-warranty-period-field .lbl {
            margin-bottom: 6px;
            color: #334155;
            font-weight: 600;
            font-size: 12px;
        }
        /* Remove extra marked divider borders in Product data panels */
        body.v2-product-create-page #p-pricing .dv,
        body.v2-product-create-page #p-pricing hr,
        body.v2-product-create-page #p-warranty .dv,
        body.v2-product-create-page #p-warranty hr {
            border: 0 !important;
            display: none !important;
            height: 0 !important;
            margin: 0 !important;
            padding: 0 !important;
        }
        body.v2-product-create-page #p-warranty .tr:last-child {
            border-bottom: 0 !important;
        }
        /* Variations block old-page style polish */
        .v2-variation-oldlike {
            min-height: 360px;
            padding: 10px 2px 14px;
        }
        .v2-variation-oldlike .v2-var-title {
            margin-bottom: 14px;
            font-size: 20px;
            font-weight: 600;
            color: #0f172a;
            letter-spacing: .1px;
        }
        .v2-variation-oldlike .v2-var-help {
            color: #0f172a;
            font-size: 15px;
            margin: 4px 0 12px;
            font-weight: 500;
        }
        .v2-variation-oldlike .v2-var-key {
            height: 40px;
            border: 1px solid #d7e2ee;
            border-radius: 8px;
            background: #f8fafc;
            color: #3b4f66;
            font-weight: 600;
            display: flex;
            align-items: center;
            padding: 0 14px;
        }
        .v2-variation-oldlike .bootstrap-select > .dropdown-toggle {
            height: 40px;
            color: #334155 !important;
            padding-left: 14px;
        }
        .v2-variation-oldlike .bootstrap-select > .dropdown-toggle .filter-option {
            display: flex;
            align-items: center;
            color: #334155 !important;
        }
        /* Variation control focus/border is unified by the single global master control system. */
        .v2-variation-oldlike .bootstrap-select.open {
            z-index: 1080;
        }
        .v2-variation-oldlike .form-group.row.gutters-5 {
            margin-bottom: 12px;
        }
        .v2-variation-oldlike .cibato-switch {
            margin-top: 9px;
        }
        .v2-variation-oldlike table {
            margin-top: 10px;
            border-color: #dbe5ef;
        }
        .v2-variation-oldlike table th {
            background: #f8fbff;
            font-weight: 600;
        }
        .v2-variation-oldlike table input.form-control {
            height: 34px;
        }
        .v2-variation-oldlike .f {
            margin-bottom: 10px;
        }

        /* Global V2 hover system (match Product categories hover tone) */
        body.v2-product-create-page {
            --v2-hover-bg: #f5f9ff;
            --v2-hover-text: #0f4c81;
            /* Keep dropdown hover/selected fully aligned with category hover tone */
            --v2-dropdown-active-bg: #f5f9ff;
            --v2-dropdown-active-text: #0f4c81;
        }
        body.v2-product-create-page .v2-cat-box .ci:hover,
        body.v2-product-create-page .v2-cat-box .ci:focus-within {
            background: var(--v2-hover-bg) !important;
            color: var(--v2-hover-text) !important;
        }
        body.v2-product-create-page .v2-cat-box .cat-tabs a:hover,
        body.v2-product-create-page .v2-cat-box .cat-tabs button:hover,
        body.v2-product-create-page .v2-brand-box a:hover {
            background: var(--v2-hover-bg) !important;
            color: var(--v2-hover-text) !important;
        }
        /* Avoid double-border on bootstrap-select wrappers */
        body.v2-product-create-page .bootstrap-select {
            border: 0 !important;
            box-shadow: none !important;
            background: transparent !important;
            padding: 0 !important;
        }
        body.v2-product-create-page .bootstrap-select .dropdown-toggle {
            width: 100%;
            min-height: 40px;
            border: 1px solid #d7e2ee !important;
            border-radius: 8px !important;
            box-shadow: none !important;
            background: #fff !important;
        }
        body.v2-product-create-page .bootstrap-select .dropdown-toggle .filter-option {
            display: flex;
            align-items: center;
        }
        /* Flash sale: Campaign on first row, Campaign discount directly below (not side-by-side) */
        body.v2-product-create-page #p-pricing .v2-flash-sale-row {
            margin-bottom: 12px;
            display: grid !important;
            grid-template-columns: minmax(0, 1fr);
            gap: 14px;
            align-content: start;
            justify-items: stretch;
        }
        body.v2-product-create-page #p-pricing .v2-flash-sale-row > .f {
            margin-bottom: 0;
            width: 100%;
            max-width: min(420px, 100%);
        }
        /* Inner row (amount + % / flat picker) stays one line, compact — only inside discount field */
        body.v2-product-create-page #p-pricing .v2-flash-sale-discount-field .r2 {
            display: flex;
            flex-wrap: wrap;
            align-items: flex-end;
            gap: 10px;
        }
        body.v2-product-create-page #p-pricing .v2-flash-sale-discount-field .r2 > * {
            flex: 1 1 auto;
            min-width: min(112px, 100%);
            max-width: 100%;
        }
        body.v2-product-create-page #p-pricing .v2-flash-sale-discount-field .v2-campaign-discount-input {
            flex: 1 1 120px !important;
            max-width: 200px;
        }
        body.v2-product-create-page #p-pricing .v2-flash-sale-discount-field .bootstrap-select {
            flex: 0 1 160px;
            min-width: 130px;
            max-width: 220px;
        }
        /* Campaign + Campaign discount: exact same as global control border system */
        /* Control border system is unified by the single global master block at file end. */
        /* Date range calendar popup polish */
        body.v2-product-create-page .daterangepicker {
            border: 1px solid #d7e3ef !important;
            border-radius: 12px !important;
            box-shadow: 0 14px 30px rgba(15, 23, 42, 0.12) !important;
            overflow: hidden;
            font-family: inherit;
        }
        body.v2-product-create-page .daterangepicker:before,
        body.v2-product-create-page .daterangepicker:after {
            border-bottom-color: #d7e3ef !important;
        }
        body.v2-product-create-page .daterangepicker .calendar-table {
            border: 0 !important;
            background: #ffffff !important;
        }
        body.v2-product-create-page .daterangepicker .calendar-table th,
        body.v2-product-create-page .daterangepicker .calendar-table td {
            border-radius: 7px;
            border: 0 !important;
            width: 30px;
            height: 30px;
            line-height: 30px;
            font-size: 12px;
        }
        body.v2-product-create-page .daterangepicker th.month {
            color: #0f172a;
            font-size: 14px;
            font-weight: 700;
        }
        body.v2-product-create-page .daterangepicker td.available:hover,
        body.v2-product-create-page .daterangepicker th.available:hover {
            background: #f5f9ff !important;
            color: #0f4c81 !important;
        }
        body.v2-product-create-page .daterangepicker td.in-range {
            background: #edf6ff !important;
            color: #0f4c81 !important;
        }
        body.v2-product-create-page .daterangepicker td.active,
        body.v2-product-create-page .daterangepicker td.active:hover,
        body.v2-product-create-page .daterangepicker td.start-date,
        body.v2-product-create-page .daterangepicker td.end-date {
            background: #009ef7 !important;
            color: #fff !important;
        }
        body.v2-product-create-page .daterangepicker td.off,
        body.v2-product-create-page .daterangepicker td.off.in-range,
        body.v2-product-create-page .daterangepicker td.off.start-date,
        body.v2-product-create-page .daterangepicker td.off.end-date {
            color: #a6b3c2 !important;
            background: #fff !important;
        }
        body.v2-product-create-page .daterangepicker .drp-buttons {
            border-top: 1px solid #e2ebf3 !important;
            background: #f8fbff;
            padding: 10px 12px;
        }
        body.v2-product-create-page .daterangepicker .drp-selected {
            color: #475569;
            font-weight: 500;
            font-size: 12px;
        }
        body.v2-product-create-page .daterangepicker .drp-buttons .btn {
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
            padding: 6px 12px;
            border: 1px solid #d2deea;
        }
        body.v2-product-create-page .daterangepicker .drp-buttons .btn-primary {
            background: #009ef7;
            border-color: #009ef7;
            color: #fff;
        }
        body.v2-product-create-page .daterangepicker .drp-buttons .btn-default,
        body.v2-product-create-page .daterangepicker .drp-buttons .cancelBtn {
            background: #fff;
            color: #334155;
        }
        /* Datepicker select controls inherit the global master control system. */
        /* Variation controls are unified by the single global master control block at file end. */
        /* Stable left-right exact match in variations top rows */
        body.v2-product-create-page #p-variations .form-group.row.gutters-5 {
            align-items: center !important;
        }
        /* Control border system is now unified by the single global master block at file end. */
        /* dropdown interaction color is enforced by the single global block at file end */
    </style>

    <form id="v2RealSubmitForm" action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data" style="display:none;">
        @csrf
        <input type="hidden" name="button" id="v2_button_action" value="publish">
        <input type="hidden" name="name" id="v2_name">
        <input type="hidden" name="slug" id="v2_slug">
        <input type="hidden" name="category_id" id="v2_category_id">
        <input type="hidden" name="unit_price" id="v2_unit_price">
        <input type="hidden" name="discount" id="v2_discount">
        <input type="hidden" name="discount_type" id="v2_discount_type" value="percent">
        <input type="hidden" name="current_stock" id="v2_current_stock" value="0">
        <input type="hidden" name="low_stock_quantity" id="v2_low_stock_quantity" value="1">
        <input type="hidden" name="stock_visibility_state" id="v2_stock_visibility_state" value="quantity">
        <input type="hidden" name="sku" id="v2_sku">
        <input type="hidden" name="barcode" id="v2_barcode">
        <input type="hidden" name="description" id="v2_description">
        <input type="hidden" name="short_description" id="v2_short_description">
        <input type="hidden" name="meta_title" id="v2_meta_title">
        <input type="hidden" name="meta_description" id="v2_meta_description">
        <input type="hidden" name="thumbnail_img" id="v2_thumbnail_img">
        <input type="hidden" name="photos" id="v2_photos">
        <input type="hidden" name="short_video" id="v2_short_video">
        <input type="hidden" name="short_video_thumbnail" id="v2_short_video_thumbnail">
        <input type="hidden" name="pdf" id="v2_pdf">
        <input type="hidden" name="meta_img" id="v2_meta_img">
        <input type="hidden" name="brand_id" id="v2_brand_id">
        <input type="hidden" name="external_link" id="v2_external_link">
        <input type="hidden" name="external_link_btn" id="v2_external_link_btn">
        <input type="hidden" name="unit" id="v2_unit">
        <input type="hidden" name="min_qty" id="v2_min_qty">
        <input type="hidden" name="weight" id="v2_weight">
        <input type="hidden" name="flash_deal_id" id="v2_flash_deal_id">
        <input type="hidden" name="flash_discount" id="v2_flash_discount">
        <input type="hidden" name="flash_discount_type" id="v2_flash_discount_type" value="percent">
        <input type="hidden" name="date_range" id="v2_date_range">
        <input type="hidden" name="shipping_type" id="v2_shipping_type" value="free">
        <input type="hidden" name="flat_shipping_cost" id="v2_flat_shipping_cost" value="0">
        <input type="hidden" name="est_shipping_days" id="v2_est_shipping_days">
        <input type="hidden" name="show_estimated_shipping_time" id="v2_show_estimated_shipping_time" value="0">
        <input type="hidden" name="show_shipping_note" id="v2_show_shipping_note" value="0">
        <input type="hidden" name="is_quantity_multiplied" id="v2_is_quantity_multiplied" value="0">
        <input type="hidden" name="cash_on_delivery" id="v2_cash_on_delivery" value="0">
        <input type="hidden" name="show_delivery_notes" id="v2_show_delivery_notes" value="0">
        <input type="hidden" name="show_smart_bar" id="v2_show_smart_bar" value="0">
        <input type="hidden" name="has_warranty" id="v2_has_warranty" value="0">
        <input type="hidden" name="warranty_id" id="v2_warranty_id" value="">
        <input type="hidden" name="show_warranty_note" id="v2_show_warranty_note" value="0">
        <input type="hidden" name="show_review" id="v2_show_review" value="1">
        <input type="hidden" name="published" id="v2_published" value="1">
        <input type="hidden" name="featured" id="v2_featured" value="0">
        <input type="hidden" name="todays_deal" id="v2_todays_deal" value="0">
        <input type="hidden" name="frequently_bought_selection_type" id="v2_fq_selection_type" value="product">
        <input type="hidden" name="fq_bought_product_category_id" id="v2_fq_category_id" value="">
        <div id="v2_category_ids_wrap"></div>
        <div id="v2_video_links_wrap"></div>
        <div id="v2_meta_keywords_wrap"></div>
        <div id="v2_tags_wrap"></div>
        <div id="v2_variant_wrap"></div>
        <div id="v2_fq_products_wrap"></div>

        <!-- hidden uploader bridge -->
        <div style="position:fixed;left:-9999px;top:-9999px;opacity:0;pointer-events:none;">
            <div id="v2_pick_thumbnail" data-toggle="cibatouploader" data-type="image"><input type="hidden" class="selected-files"></div>
            <div id="v2_pick_gallery" data-toggle="cibatouploader" data-type="image" data-multiple="true"><input type="hidden" class="selected-files"></div>
            <div id="v2_pick_video" data-toggle="cibatouploader" data-type="video" data-multiple="true"><input type="hidden" class="selected-files"></div>
            <div id="v2_pick_video_thumb" data-toggle="cibatouploader" data-type="image"><input type="hidden" class="selected-files"></div>
            <div id="v2_pick_pdf" data-toggle="cibatouploader" data-type="document"><input type="hidden" class="selected-files"></div>
            <div id="v2_pick_meta_img" data-toggle="cibatouploader" data-type="image"><input type="hidden" class="selected-files"></div>
        </div>
    </form>

    <script>
        (function () {
            document.body.classList.add('v2-product-create-page');
            const categoryMap = @json($categoryNameToId);
            const brands = @json($brands->map(fn($b) => ['id' => $b->id, 'name' => $b->name])->values());
            const categories = @json($flatCategories);
            const colors = @json($colors->map(fn($c) => ['name' => $c->name, 'code' => $c->code])->values());
            const attributeMap = @json($attributeNameToId);
            const attributesList = @json($attributes->map(fn($a) => ['id' => $a->id, 'name' => $a->getTranslation('name')])->values());
            const flashDeals = @json($flashDeals->map(fn($f) => ['id' => $f->id, 'title' => $f->title])->values());
            const linkedProducts = @json($linkedProducts->map(fn($p) => ['id' => $p->id, 'name' => $p->name])->values());
            const warrantyOptions = @json($warrantyOptions->map(fn($w) => ['id' => $w->id, 'text' => $w->text])->values());
            const STRICT_ONE_TO_ONE = false;

            function textOf(el) {
                return (el?.textContent || '').trim();
            }
            function findSidebarBoxByTitle(partialTitle) {
                const lower = partialTitle.toLowerCase();
                return Array.from(document.querySelectorAll('.sb')).find(sb =>
                    (sb.querySelector('.sbhd h3')?.textContent || '').toLowerCase().includes(lower)
                );
            }
            function checkboxInPanelByText(root, partialText) {
                if (!root) return null;
                const lower = partialText.toLowerCase();
                return Array.from(root.querySelectorAll('label.co, label.ro, label'))
                    .find(l => (l.textContent || '').toLowerCase().includes(lower))
                    ?.querySelector('input[type="checkbox"], input[type="radio"]') || null;
            }
            function findMainBoxByTitle(partialTitle) {
                const lower = partialTitle.toLowerCase();
                const heads = Array.from(document.querySelectorAll('.mhd h3, .mhd h2, .mbox h3, .mbox h2'));
                const head = heads.find(h => (h.textContent || '').toLowerCase().includes(lower));
                return head ? (head.closest('.mbox') || head.closest('section') || head.parentElement) : null;
            }
            function openUploaderBridge(bridgeSelector, type, multiple) {
                const bridge = document.querySelector(bridgeSelector);
                if (!bridge || typeof CIBATO === 'undefined' || !CIBATO.uploader) return;
                const selected = bridge.querySelector('.selected-files')?.value || '';
                CIBATO.uploader.trigger(
                    bridge,
                    'direct',
                    type || 'all',
                    selected,
                    !!multiple,
                    function(selectedIds){
                        const val = Array.isArray(selectedIds) ? selectedIds.join(',') : (selectedIds || '');
                        const input = bridge.querySelector('.selected-files');
                        if (input) input.value = val;
                        // Route refresh by uploader type to avoid extra AJAX and preview delay.
                        if (bridgeSelector === '#v2_pick_video' || bridgeSelector === '#v2_pick_video_thumb') {
                            refreshVideoMediaPreview();
                        } else if (bridgeSelector === '#v2_pick_pdf') {
                            refreshPdfPreview();
                        } else if (bridgeSelector === '#v2_pick_meta_img') {
                            refreshSeoMediaPreview();
                        } else {
                            refreshMediaPreviews();
                        }
                    }
                );
            }

            function fetchFilesByIds(ids, done) {
                if (!ids || !ids.length || !window.jQuery || typeof CIBATO === 'undefined') {
                    done([]);
                    return;
                }
                $.post(
                    CIBATO.data.appUrl + "/cibato-uploader/get_file_by_ids",
                    { _token: CIBATO.data.csrf, ids: ids.join(',') },
                    function (data) { done(Array.isArray(data) ? data : []); }
                ).fail(function () { done([]); });
            }

            function refreshMediaPreviews() {
                const imageBox = findSidebarBoxByTitle('product image');
                const galleryBox = findSidebarBoxByTitle('product gallery');
                const thumbDrop = imageBox ? imageBox.querySelector('.idrop') : null;
                const galleryDrop = galleryBox ? galleryBox.querySelector('.idrop') : null;
                const galleryGrid = galleryBox ? galleryBox.querySelector('.gg') : null;
                const galleryHint = galleryDrop ? galleryDrop.querySelector('small') : null;
                const reorderHint = galleryBox ? galleryBox.querySelector('.sbft') : null;

                const thumbIds = (document.querySelector('#v2_pick_thumbnail .selected-files')?.value || '')
                    .split(',').map(v => parseInt(v, 10)).filter(Boolean);
                const galleryIds = (document.querySelector('#v2_pick_gallery .selected-files')?.value || '')
                    .split(',').map(v => parseInt(v, 10)).filter(Boolean);

                fetchFilesByIds(thumbIds, function(files){
                    if (!thumbDrop) return;
                    const img = files[0];
                    if (img && img.file_name) {
                        const src = (CIBATO?.uploader?.resolveUploaderUrl)
                            ? CIBATO.uploader.resolveUploaderUrl(img.file_name)
                            : img.file_name;
                        thumbDrop.innerHTML = `<div class="v2-media-thumb-wrap">
                            <img src="${src}" alt="thumbnail">
                            <button type="button" class="v2-remove-thumb" title="Remove image" aria-label="Remove thumbnail">&times;</button>
                        </div>`;
                    } else {
                        thumbDrop.innerHTML = `<i class="ti ti-camera-plus" aria-hidden="true"></i><span>Set product image</span><small>500 × 500 px · JPG or PNG</small>`;
                    }
                });

                fetchFilesByIds(galleryIds, function(files){
                    if (!galleryGrid) return;
                    if (galleryDrop) galleryDrop.style.display = '';
                    if (galleryHint) galleryHint.textContent = '800 × 800 px · Unlimited images';

                    if (!files.length) {
                        galleryGrid.innerHTML = '';
                        if (reorderHint) reorderHint.style.display = 'none';
                        return;
                    }

                    if (reorderHint) reorderHint.style.display = '';
                    galleryGrid.innerHTML = files.map((f, idx) => {
                        const src = (CIBATO?.uploader?.resolveUploaderUrl)
                            ? CIBATO.uploader.resolveUploaderUrl(f.file_name)
                            : f.file_name;
                        return `<div class="gs" draggable="true">
                            <div class="v2-media-slot-wrap">
                                <img src="${src}" alt="gallery-${idx+1}">
                                <button type="button" class="v2-remove-gallery" data-index="${idx}" title="Remove image" aria-label="Remove gallery image">&times;</button>
                            </div>
                        </div>`;
                    }).join('');
                });
            }

            function getGalleryIds() {
                return (document.querySelector('#v2_pick_gallery .selected-files')?.value || '')
                    .split(',')
                    .map(v => parseInt(v, 10))
                    .filter(Boolean);
            }

            function setGalleryIds(ids) {
                const input = document.querySelector('#v2_pick_gallery .selected-files');
                if (input) input.value = ids.join(',');
            }

            function setVideoIds(ids) {
                const input = document.querySelector('#v2_pick_video .selected-files');
                if (input) input.value = ids.join(',');
            }

            function setVideoThumbId(id) {
                const input = document.querySelector('#v2_pick_video_thumb .selected-files');
                if (input) input.value = id ? String(id) : '';
            }

            function refreshVideoMediaPreview() {
                const videoBox = Array.from(document.querySelectorAll('.sb')).find(sb => (sb.querySelector('.sbhd h3')?.textContent || '').toLowerCase().includes('product video'));
                if (!videoBox) return;

                const videoIds = (document.querySelector('#v2_pick_video .selected-files')?.value || '')
                    .split(',').map(v => parseInt(v, 10)).filter(Boolean);
                const thumbId = parseInt((document.querySelector('#v2_pick_video_thumb .selected-files')?.value || '').trim(), 10) || 0;
                const videoCard = videoBox.querySelector('.v2-video-upload-card[data-kind="video"]');
                const thumbCard = videoBox.querySelector('.v2-video-upload-card[data-kind="thumb"]');

                fetchFilesByIds(videoIds, function(videoFiles){
                    fetchFilesByIds(thumbId ? [thumbId] : [], function(thumbFiles){
                        const thumb = thumbFiles[0];
                        const thumbSrc = thumb && thumb.file_name
                            ? ((CIBATO?.uploader?.resolveUploaderUrl) ? CIBATO.uploader.resolveUploaderUrl(thumb.file_name) : thumb.file_name)
                            : '';
                        const firstVideo = videoFiles[0];
                        const firstVideoSrc = firstVideo && firstVideo.file_name
                            ? ((CIBATO?.uploader?.resolveUploaderUrl) ? CIBATO.uploader.resolveUploaderUrl(firstVideo.file_name) : firstVideo.file_name)
                            : '';
                        if (videoCard) {
                            videoCard.innerHTML = videoFiles.length
                                ? `<div class="v2-video-file-preview">
                                        <span class="v2-video-file-count">${videoFiles.length} selected</span>
                                        <video controls preload="metadata" src="${firstVideoSrc}"></video>
                                        <button type="button" class="v2-video-mini-remove" data-kind="video-remove" title="Remove videos">&times;</button>
                                   </div>`
                                : `<span>Upload video file</span><small>MP4 / MOV</small>`;
                        }
                        if (thumbCard) {
                            thumbCard.innerHTML = thumbSrc
                                ? `<div class="v2-video-thumb-preview">
                                        <img src="${thumbSrc}" alt="Video thumbnail">
                                        <button type="button" class="v2-video-mini-remove" data-kind="thumb-remove" title="Remove thumbnail">&times;</button>
                                   </div>`
                                : `<span>Upload custom thumbnail</span><small>500 × 500 px · JPG or PNG</small>`;
                        }
                    });
                });
            }

            function refreshSeoMediaPreview() {
                const seo = document.getElementById('bd-seo');
                if (!seo) return;
                const ids = (document.querySelector('#v2_pick_meta_img .selected-files')?.value || '')
                    .split(',').map(v => parseInt(v, 10)).filter(Boolean);
                fetchFilesByIds(ids, function(files){
                    const amount = seo.querySelector('.v2-seo-meta-upload .file-amount');
                    const previewBox = seo.querySelector('.v2-seo-old-preview');
                    if (!files.length) {
                        if (amount) amount.textContent = 'Choose File';
                        if (previewBox) previewBox.innerHTML = '';
                        return;
                    }
                    const file = files[0];
                    const src = (CIBATO?.uploader?.resolveUploaderUrl)
                        ? CIBATO.uploader.resolveUploaderUrl(file.file_name)
                        : file.file_name;
                    if (amount) {
                        const label = `${file.file_original_name || 'Image'}.${file.extension || 'jpg'}`;
                        amount.textContent = label;
                    }
                    if (previewBox) {
                        previewBox.innerHTML = `<img src="${src}" alt="Meta image" style="max-height:80px;display:block;">`;
                    }
                });
            }

            function refreshPdfPreview() {
                const pdfBox = document.getElementById('hd-pdf')?.closest('.mbox');
                if (!pdfBox) return;
                const ids = (document.querySelector('#v2_pick_pdf .selected-files')?.value || '')
                    .split(',').map(v => parseInt(v, 10)).filter(Boolean);
                fetchFilesByIds(ids, function(files){
                    let selected = pdfBox.querySelector('.v2-pdf-selected');
                    if (!files.length) {
                        if (selected) selected.remove();
                        const field = pdfBox.querySelector('.v2-pdf-file-field');
                        if (field) field.textContent = 'No file chosen';
                        return;
                    }
                    const file = files[0];
                    const label = `${file.file_original_name || 'Document'}.${file.extension || 'pdf'}`;
                    const field = pdfBox.querySelector('.v2-pdf-file-field');
                    if (field) field.textContent = label;
                    if (!selected) {
                        selected = document.createElement('div');
                        selected.className = 'v2-pdf-selected';
                        pdfBox.appendChild(selected);
                    }
                    selected.innerHTML = `<span style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">${label}</span><button type="button" class="v2-pdf-remove">Remove</button>`;
                });
            }

            function findInputByLabel(root, labelText) {
                const labels = root.querySelectorAll('.lbl');
                for (const lbl of labels) {
                    if (textOf(lbl).toLowerCase().includes(labelText.toLowerCase())) {
                        const field = lbl.closest('.f');
                        if (!field) continue;
                        return field.querySelector('input, textarea, select');
                    }
                }
                return null;
            }
            function findFieldByLabel(root, labelText) {
                if (!root) return null;
                const labels = root.querySelectorAll('.lbl');
                for (const lbl of labels) {
                    if (textOf(lbl).toLowerCase().includes(labelText.toLowerCase())) {
                        return lbl.closest('.f');
                    }
                }
                return null;
            }
            function ensurePricingHintNode(field, className) {
                if (!field) return null;
                let hint = field.querySelector(`.${className}`);
                if (hint) return hint;
                hint = document.createElement('div');
                hint.className = `hint ${className}`;
                field.appendChild(hint);
                return hint;
            }
            function getPrimaryPricingHintNode(field) {
                if (!field) return null;
                // Reuse existing template hint first to avoid duplicate lines.
                const existing = Array.from(field.querySelectorAll('.hint'))
                    .find(h => !h.classList.contains('v2-sale-error-hint'));
                if (existing) return existing;
                return ensurePricingHintNode(field, 'v2-sale-derived-hint');
            }
            function derivePricingState() {
                const pricing = document.getElementById('p-pricing');
                const regularInput = findInputByLabel(pricing, 'regular price');
                const saleInput = findInputByLabel(pricing, 'sale price');
                const regular = parseFloat((regularInput?.value || '').trim() || '0');
                const sale = parseFloat((saleInput?.value || '').trim() || '0');
                const hasRegular = Number.isFinite(regular) && regular > 0;
                const hasSale = Number.isFinite(sale) && sale > 0;
                const validSale = hasRegular && hasSale && sale < regular;
                const discountPercent = validSale ? (((regular - sale) / regular) * 100) : 0;
                return { pricing, regularInput, saleInput, regular, sale, hasRegular, hasSale, validSale, discountPercent };
            }
            function refreshPricingUX() {
                const { pricing, saleInput, hasRegular, hasSale, validSale, discountPercent } = derivePricingState();
                if (!pricing || !saleInput) return;
                const saleField = findFieldByLabel(pricing, 'sale price');
                const helper = getPrimaryPricingHintNode(saleField);
                const error = ensurePricingHintNode(saleField, 'v2-sale-error-hint');
                if (helper) {
                    helper.style.color = '#64748b';
                    helper.style.fontWeight = '500';
                    helper.classList.add('v2-sale-derived-hint');
                    helper.textContent = validSale
                        ? `Auto discount: ${discountPercent.toFixed(2)}% (derived from regular and sale price)`
                        : 'Leave blank if no sale is running.';
                }
                if (error) {
                    if (hasSale && (!hasRegular || !validSale)) {
                        error.textContent = !hasRegular
                            ? 'Set regular price first.'
                            : 'Sale price must be lower than regular price.';
                        error.style.color = '#dc2626';
                    } else {
                        error.textContent = '';
                    }
                }
            }

            function syncAndSubmit(mode) {
                const realForm = document.getElementById('v2RealSubmitForm');
                if (!realForm) return;
                const setVal = (id, value) => {
                    const el = document.getElementById(id);
                    if (el) el.value = value ?? '';
                };

                // Name
                setVal('v2_name', document.querySelector('.ni')?.value || '');
                setVal('v2_slug', (document.querySelector('.plslug')?.textContent || '').trim());

                // Pricing/Inventory panel fields
                const pricing = document.getElementById('p-pricing');
                const inventory = document.getElementById('p-inventory');
                const seo = document.getElementById('bd-seo');
                const desc = document.getElementById('bd-d');

                const { regularInput, saleInput, regular, sale, validSale, discountPercent } = derivePricingState();
                const regularPriceRaw = regularInput?.value || '';

                setVal('v2_unit_price', regularPriceRaw);
                if (validSale) {
                    // V2 rule: discount is always derived from sale price.
                    setVal('v2_discount', discountPercent.toFixed(2));
                    setVal('v2_discount_type', 'percent');
                } else {
                    // No valid sale price discount => keep product discount at zero.
                    setVal('v2_discount', '0');
                    setVal('v2_discount_type', 'percent');
                }
                if (sale > 0 && (!regular || sale >= regular)) {
                    alert('Sale price must be lower than regular price.');
                    if (saleInput) saleInput.focus();
                    return;
                }
                setVal('v2_external_link', findInputByLabel(pricing, 'external product url')?.value || '');
                setVal('v2_external_link_btn', findInputByLabel(pricing, 'button label')?.value || '');
                setVal('v2_date_range', findInputByLabel(pricing, 'sale date range')?.value || '');

                setVal('v2_current_stock', findInputByLabel(inventory, 'stock quantity')?.value || '0');
                setVal('v2_low_stock_quantity', findInputByLabel(inventory, 'low stock warning threshold')?.value || '1');
                setVal('v2_sku', findInputByLabel(inventory, 'sku')?.value || '');
                setVal('v2_barcode', findInputByLabel(inventory, 'barcode')?.value || '');
                const visibilityHide = checkboxInPanelByText(inventory, 'hide stock info completely')?.checked;
                const visibilityText = checkboxInPanelByText(inventory, 'show status text only')?.checked;
                const visibilityQty = checkboxInPanelByText(inventory, 'show exact quantity number')?.checked;
                const visibilityState = visibilityHide ? 'hide' : (visibilityText ? 'text' : (visibilityQty ? 'quantity' : 'quantity'));
                setVal('v2_stock_visibility_state', visibilityState);

                let descriptionHtml = '';
                if (window.jQuery && $('#v2_main_editor').length && typeof $('#v2_main_editor').summernote === 'function') {
                    descriptionHtml = $('#v2_main_editor').summernote('code') || '';
                }
                if (!descriptionHtml) {
                    descriptionHtml = textOf(desc?.querySelector('.ea'));
                }
                setVal('v2_description', descriptionHtml);
                let shortDescHtml = '';
                if (window.jQuery && $('#v2_short_desc_editor').length && typeof $('#v2_short_desc_editor').summernote === 'function') {
                    shortDescHtml = $('#v2_short_desc_editor').summernote('code') || '';
                }
                setVal('v2_short_description', shortDescHtml);
                // Backend product flow primarily persists `description`.
                // If main description is empty, preserve user content from short description.
                if (!String(descriptionHtml || '').replace(/<[^>]*>/g, '').trim() && String(shortDescHtml || '').replace(/<[^>]*>/g, '').trim()) {
                    setVal('v2_description', shortDescHtml);
                }
                setVal('v2_meta_title', findInputByLabel(seo, 'meta title')?.value || '');
                setVal('v2_meta_description', findInputByLabel(seo, 'description')?.value || '');
                const keywordsFromInput = findInputByLabel(seo, 'keywords')?.value || findInputByLabel(seo, 'tags')?.value || '';
                const seoPills = Array.from((seo || document).querySelectorAll('.pill'))
                    .map(p => (p.textContent || '').replace('×', '').trim())
                    .filter(Boolean);
                const keywordValues = (keywordsFromInput || seoPills.join(','))
                    .split(',')
                    .map(v => v.trim())
                    .filter(Boolean);
                const keywordsWrap = document.getElementById('v2_meta_keywords_wrap');
                keywordsWrap.innerHTML = '';
                keywordValues.forEach(k => {
                    const i = document.createElement('input');
                    i.type = 'hidden';
                    i.name = 'meta_keywords[]';
                    i.value = k;
                    keywordsWrap.appendChild(i);
                });

                setVal('v2_unit', findInputByLabel(inventory, 'unit')?.value || '');
                setVal('v2_min_qty', findInputByLabel(inventory, 'min. purchase quantity')?.value || '1');
                setVal('v2_weight', findInputByLabel(inventory, 'weight')?.value || '');

                // Flash sale mapping
                const campaignSelect = findInputByLabel(pricing, 'campaign');
                setVal('v2_flash_deal_id', campaignSelect?.value || '');
                const campaignDiscountField = findFieldByLabel(pricing, 'campaign discount');
                const campaignDiscountInput = campaignDiscountField?.querySelector('input, textarea');
                const campaignDiscountTypeSelect = campaignDiscountField?.querySelector('select');
                setVal('v2_flash_discount', campaignDiscountInput?.value || '');
                setVal('v2_flash_discount_type', (campaignDiscountTypeSelect?.value || '').includes('Fixed') ? 'amount' : 'percent');
                const shippingPanel = document.getElementById('p-shipping');
                const deliveryTimeVal = String(findInputByLabel(shippingPanel, 'delivery time text')?.value || '').replace(/\s+/g, ' ').trim();
                setVal('v2_est_shipping_days', deliveryTimeVal);
                setVal('v2_show_estimated_shipping_time', checkboxInPanelByText(shippingPanel, 'show estimated delivery time')?.checked ? '1' : '0');
                setVal('v2_show_shipping_note', checkboxInPanelByText(shippingPanel, 'show in the shipping information')?.checked ? '1' : '0');
                const shippingTypeRadio = shippingPanel?.querySelector('input[type="radio"]:checked');
                setVal('v2_shipping_type', shippingTypeRadio?.value || 'free');
                const shippingCostRaw = String(findInputByLabel(shippingPanel, 'shipping cost')?.value || '0').trim();
                const shippingCostNum = Number(shippingCostRaw);
                setVal('v2_flat_shipping_cost', Number.isFinite(shippingCostNum) && shippingCostNum >= 0 ? String(shippingCostNum) : '0');
                setVal('v2_is_quantity_multiplied', checkboxInPanelByText(shippingPanel, 'multiply')?.checked ? '1' : '0');

                // Brand mapping by selected text (Brand sidebar box only)
                const brandBox = Array.from(document.querySelectorAll('.sb')).find(sb => (sb.querySelector('.sbhd h3')?.textContent || '').toLowerCase().includes('brand'));
                const brandSelect = brandBox ? brandBox.querySelector('select') : null;
                let brandId = '';
                if (brandSelect) {
                    const selected = brandSelect.options[brandSelect.selectedIndex]?.text?.trim().toLowerCase();
                    const matched = brands.find(b => (b.name || '').trim().toLowerCase() === selected);
                    brandId = matched ? matched.id : '';
                }
                setVal('v2_brand_id', brandId);

                // Category mapping
                const checkedCats = Array.from(document.querySelectorAll('.ci input[type="checkbox"]:checked'))
                    .map(cb => cb.closest('.ci')?.textContent?.trim())
                    .filter(Boolean)
                    .map(name => categoryMap[name.toLowerCase()])
                    .filter(Boolean);

                const uniqueCatIds = [...new Set(checkedCats)];
                const catWrap = document.getElementById('v2_category_ids_wrap');
                catWrap.innerHTML = '';
                const effectiveCatIds = uniqueCatIds.length ? uniqueCatIds : (categories[0] ? [categories[0].id] : []);
                effectiveCatIds.forEach(id => {
                    const inp = document.createElement('input');
                    inp.type = 'hidden';
                    inp.name = 'category_ids[]';
                    inp.value = String(id);
                    catWrap.appendChild(inp);
                });
                setVal('v2_category_id', effectiveCatIds[0] || '');

                // Publish toggles from sidebar
                setVal('v2_featured', document.getElementById('sw-ft')?.classList.contains('on') ? '1' : '0');
                setVal('v2_todays_deal', document.getElementById('sw-td')?.classList.contains('on') ? '1' : '0');
                setVal('v2_cash_on_delivery', document.getElementById('sw-cod')?.classList.contains('on') ? '1' : '0');
                setVal('v2_has_warranty', document.getElementById('sw-w')?.classList.contains('on') ? '1' : '0');
                setVal('v2_show_smart_bar', document.getElementById('sw-sb')?.classList.contains('on') ? '1' : '0');
                setVal('v2_warranty_id', findInputByLabel(document.getElementById('p-warranty'), 'warranty period')?.value || '');
                setVal('v2_show_delivery_notes', checkboxInPanelByText(document.getElementById('p-warranty'), 'show cod notes')?.checked ? '1' : '0');
                setVal('v2_show_warranty_note', checkboxInPanelByText(document.getElementById('p-warranty'), 'show warranty notes')?.checked ? '1' : '0');
                setVal('v2_show_review', document.getElementById('sw-rv')?.classList.contains('on') ? '1' : '0');

                if (document.getElementById('v2_has_warranty')?.value !== '1') {
                    setVal('v2_warranty_id', '');
                    setVal('v2_show_warranty_note', '0');
                }
                if (document.getElementById('v2_cash_on_delivery')?.value !== '1') {
                    setVal('v2_show_delivery_notes', '0');
                }
                setVal('v2_button_action', mode === 'draft' ? 'draft' : 'publish');
                if (!document.getElementById('v2_shipping_type')?.value) setVal('v2_shipping_type', 'free');
                if (!document.getElementById('v2_flat_shipping_cost')?.value) setVal('v2_flat_shipping_cost', '0');

                // media values from hidden uploader bridge
                setVal('v2_thumbnail_img', document.querySelector('#v2_pick_thumbnail .selected-files')?.value || '');
                setVal('v2_photos', document.querySelector('#v2_pick_gallery .selected-files')?.value || '');
                setVal('v2_short_video', document.querySelector('#v2_pick_video .selected-files')?.value || '');
                setVal('v2_short_video_thumbnail', document.querySelector('#v2_pick_video_thumb .selected-files')?.value || '');
                setVal('v2_pdf', document.querySelector('#v2_pick_pdf .selected-files')?.value || '');
                setVal('v2_meta_img', document.querySelector('#v2_pick_meta_img .selected-files')?.value || '');

                // video links
                const linksWrap = document.getElementById('v2_video_links_wrap');
                linksWrap.innerHTML = '';
                const videoBox = Array.from(document.querySelectorAll('.sb')).find(sb => (sb.querySelector('.sbhd h3')?.textContent || '').toLowerCase().includes('product video'));
                const videoLinkInput = videoBox ? videoBox.querySelector('input[placeholder*="youtube"], input[placeholder*="YouTube"], textarea') : null;
                const rawLinks = (videoLinkInput?.value || '')
                    .split(/[\n,]+/)
                    .map(v => v.trim())
                    .filter(Boolean);
                rawLinks.forEach(link => {
                    const inp = document.createElement('input');
                    inp.type = 'hidden';
                    inp.name = 'video_link[]';
                    inp.value = link;
                    linksWrap.appendChild(inp);
                });

                // product tags
                const tagsWrap = document.getElementById('v2_tags_wrap');
                tagsWrap.innerHTML = '';
                const tagsBox = findSidebarBoxByTitle('product tags');
                const tagsInput = tagsBox ? tagsBox.querySelector('input') : null;
                const tagsFromInput = (tagsInput?.value || '').split(',').map(t => t.trim()).filter(Boolean);
                const tagsFromPills = tagsBox
                    ? Array.from(tagsBox.querySelectorAll('.pill')).map(p => (p.textContent || '').replace('×', '').trim()).filter(Boolean)
                    : [];
                const allTags = [...new Set([ ...tagsFromPills, ...tagsFromInput ])];
                allTags.forEach(tag => {
                    const inp = document.createElement('input');
                    inp.type = 'hidden';
                    inp.name = 'tags[]';
                    inp.value = tag;
                    tagsWrap.appendChild(inp);
                });

                // Linked products -> map to frequently bought payload expected by backend.
                const linkedPanel = findMainBoxByTitle('linked products') || document.getElementById('p-linked');
                const fqWrap = document.getElementById('v2_fq_products_wrap');
                fqWrap.innerHTML = '';
                let linkedIds = [];
                if (linkedPanel) {
                    const checkedFromList = Array.from(linkedPanel.querySelectorAll('input[name="fq_bought_product_id"]:checked'))
                        .map(cb => parseInt(cb.value, 10))
                        .filter(Boolean);
                    const hiddenExisting = Array.from(linkedPanel.querySelectorAll('input[name="fq_bought_product_ids[]"]'))
                        .map(i => parseInt(i.value, 10))
                        .filter(Boolean);
                    const selectedFromEnhanced = Array.from(linkedPanel.querySelectorAll('select.v2-linked-select'))
                        .flatMap(sel => Array.from(sel.selectedOptions || []).map(o => parseInt(o.value, 10)))
                        .filter(Boolean);
                    const typedIds = Array.from(linkedPanel.querySelectorAll('input[type="text"], textarea'))
                        .flatMap(i => (i.value || '').split(/[^0-9]+/g))
                        .map(v => parseInt(v, 10))
                        .filter(Boolean);
                    linkedIds = [...new Set([...checkedFromList, ...hiddenExisting, ...selectedFromEnhanced, ...typedIds])];

                    const selectedCategory = linkedPanel.querySelector('select[name="fq_bought_product_category_id"]');
                    setVal('v2_fq_category_id', selectedCategory?.value || '');
                    // V2 linked products map to product-based frequently bought items.
                    setVal('v2_fq_selection_type', 'product');
                } else {
                    setVal('v2_fq_selection_type', 'product');
                    setVal('v2_fq_category_id', '');
                }
                linkedIds.forEach(id => {
                    const i = document.createElement('input');
                    i.type = 'hidden';
                    i.name = 'fq_bought_product_ids[]';
                    i.value = String(id);
                    fqWrap.appendChild(i);
                });

                // Variants & attributes binding
                const variantWrap = document.getElementById('v2_variant_wrap');
                variantWrap.innerHTML = '';
                const variationsPanel = document.getElementById('p-variations');
                const v2ColorsActive = document.getElementById('v2_colors_active');
                const v2ColorsSelect = document.getElementById('v2_colors');
                const v2ChoicesWrap = document.getElementById('v2_customer_choice_options');
                const v2SkuBox = document.getElementById('v2_sku_combination');
                const isLegacyVariationMode = !!(v2ColorsSelect && v2ChoicesWrap);

                if (isLegacyVariationMode) {
                    const normalizeVariantToken = (v) => String(v || '')
                        .replace(/\\+"/g, '"')
                        .replace(/^['"]+|['"]+$/g, '')
                        .replace(/\s+/g, ' ')
                        .trim();
                    const selectedColors = Array.from(v2ColorsSelect.selectedOptions || [])
                        .map(o => normalizeVariantToken(o.value))
                        .filter(Boolean);
                    if (v2ColorsActive?.checked && selectedColors.length) {
                        const active = document.createElement('input');
                        active.type = 'hidden';
                        active.name = 'colors_active';
                        active.value = '1';
                        variantWrap.appendChild(active);
                        selectedColors.forEach(code => {
                            const c = document.createElement('input');
                            c.type = 'hidden';
                            c.name = 'colors[]';
                            c.value = code;
                            variantWrap.appendChild(c);
                        });
                    }

                    const choiceNoInputs = Array.from(v2ChoicesWrap.querySelectorAll('input[name="choice_no[]"]'));
                    const seenChoiceNo = new Set();
                    choiceNoInputs.forEach(noInput => {
                        const attrId = (noInput.value || '').trim();
                        if (!attrId || seenChoiceNo.has(attrId)) return;
                        seenChoiceNo.add(attrId);
                        const n = document.createElement('input');
                        n.type = 'hidden';
                        n.name = 'choice_no[]';
                        n.value = attrId;
                        variantWrap.appendChild(n);
                        const choiceSelect = v2ChoicesWrap.querySelector(`select[name="choice_options_${attrId}[]"]`);
                        const seenValues = new Set();
                        Array.from(choiceSelect?.selectedOptions || []).forEach(opt => {
                            const cleanVal = normalizeVariantToken(opt.value);
                            if (!cleanVal || seenValues.has(cleanVal.toLowerCase())) return;
                            seenValues.add(cleanVal.toLowerCase());
                            const ci = document.createElement('input');
                            ci.type = 'hidden';
                            ci.name = `choice_options_${attrId}[]`;
                            ci.value = cleanVal;
                            variantWrap.appendChild(ci);
                        });
                    });

                    Array.from(v2SkuBox?.querySelectorAll('input') || []).forEach(input => {
                        const name = input.getAttribute('name') || '';
                        if (!/^(price_|qty_|sku_|img_)/.test(name)) return;
                        const clone = document.createElement('input');
                        clone.type = 'hidden';
                        clone.name = name;
                        clone.value = input.value || '';
                        variantWrap.appendChild(clone);
                    });
                } else {
                    const colorSelect = findInputByLabel(variationsPanel, 'select colors');
                    const colorCodesFromSelect = Array.from(colorSelect?.selectedOptions || [])
                        .map(o => (o.value || '').trim())
                        .filter(v => v && !v.startsWith('—'));
                    const colorCodes = [...new Set([...colorCodesFromSelect])];
                    if (colorCodes.length) {
                        const active = document.createElement('input');
                        active.type = 'hidden';
                        active.name = 'colors_active';
                        active.value = '1';
                        variantWrap.appendChild(active);
                        colorCodes.forEach(code => {
                            const c = document.createElement('input');
                            c.type = 'hidden';
                            c.name = 'colors[]';
                            c.value = code;
                            variantWrap.appendChild(c);
                        });
                    }

                    const sizeAttrId = attributeMap['size'] || null;
                    const sizeSelect = findInputByLabel(variationsPanel, 'select sizes');
                    const sizeValuesFromSelect = Array.from(sizeSelect?.selectedOptions || [])
                        .map(o => (o.value || o.textContent || '').trim())
                        .filter(v => v && !v.startsWith('—'));
                    const sizeValues = [...new Set([...sizeValuesFromSelect])];
                    if (sizeAttrId && sizeValues.length) {
                        const a = document.createElement('input');
                        a.type = 'hidden';
                        a.name = 'choice_attributes[]';
                        a.value = String(sizeAttrId);
                        variantWrap.appendChild(a);
                        sizeValues.forEach(sv => {
                            const v = document.createElement('input');
                            v.type = 'hidden';
                            v.name = `choice_options_${sizeAttrId}[]`;
                            v.value = sv;
                            variantWrap.appendChild(v);
                        });
                    }

                    // Old-contract variant payload: generate from selected colors/sizes only.
                    const variantRows = Array.from(variationsPanel?.querySelectorAll('table tbody tr') || []);
                    const rowInputs = variantRows.map((row) => {
                        const cells = row.querySelectorAll('td');
                        return {
                            price: cells[2]?.querySelector('input')?.value || '',
                            qty: cells[3]?.querySelector('input')?.value || '',
                            sku: cells[4]?.querySelector('input')?.value || ''
                        };
                    });
                    const combos = [];
                    if (colorCodes.length && sizeValues.length) {
                        colorCodes.forEach(code => sizeValues.forEach(size => combos.push({ code, size })));
                    } else if (colorCodes.length) {
                        colorCodes.forEach(code => combos.push({ code, size: '' }));
                    } else if (sizeValues.length) {
                        sizeValues.forEach(size => combos.push({ code: '', size }));
                    }
                    combos.forEach((combo, idx) => {
                        const colorName = combo.code
                            ? (colors.find(c => (c.code || '').toLowerCase() === String(combo.code).toLowerCase())?.name || combo.code)
                            : '';
                        const colorPart = String(colorName || '').replace(/\s+/g, '');
                        const sizePart = String(combo.size || '').replace(/\s+/g, '');
                        let variantKey = '';
                        if (colorPart && sizePart) variantKey = `${colorPart}-${sizePart}`;
                        else if (colorPart) variantKey = colorPart;
                        else if (sizePart) variantKey = sizePart;
                        if (!variantKey) return;
                        const safeKey = variantKey.replace(/\./g, '_');
                        const rowVal = rowInputs[idx] || {};

                        const p = document.createElement('input');
                        p.type = 'hidden';
                        p.name = `price_${safeKey}`;
                        p.value = rowVal.price || document.getElementById('v2_unit_price')?.value || '0';
                        variantWrap.appendChild(p);

                        const q = document.createElement('input');
                        q.type = 'hidden';
                        q.name = `qty_${safeKey}`;
                        q.value = rowVal.qty || '0';
                        variantWrap.appendChild(q);

                        const s = document.createElement('input');
                        s.type = 'hidden';
                        s.name = `sku_${safeKey}`;
                        const rawSku = (rowVal.sku || '').trim();
                        s.value = (!rawSku || rawSku.toLowerCase() === 'auto') ? variantKey : rawSku;
                        variantWrap.appendChild(s);
                    });
                }

                const priceVal = parseFloat(document.getElementById('v2_unit_price').value || '0');
                if (mode !== 'draft' && !(priceVal > 0)) {
                    alert('Regular price must be greater than 0.');
                    return;
                }

                // Draft route uses relaxed validation
                realForm.action = mode === 'draft' ? @json(route('products.store_as_draft')) : @json(route('products.store'));
                const formData = new FormData(realForm);
                fetch(realForm.action, {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(async (res) => {
                    const payload = await res.json().catch(() => ({}));
                    if (!res.ok || payload?.success === false) {
                        const errMsg = payload?.message || payload?.technical_message || 'Failed to save product.';
                        throw new Error(errMsg);
                    }
                    const redirectUrl = payload?.redirect || @json(route('products.admin'));
                    window.location.href = redirectUrl;
                })
                .catch((err) => {
                    const msg = String(err?.message || 'Failed to save product.');
                    if (window.CIBATO?.plugins?.notify) {
                        CIBATO.plugins.notify('danger', msg);
                    } else {
                        alert(msg);
                    }
                });
            }

            // Hydrate and enhance category checklist (search + tabs + larger panel).
            (function initCategorySection() {
                const catBox = findSidebarBoxByTitle('product categories');
                if (!catBox) return;
                catBox.classList.add('v2-cat-box');
                Array.from(catBox.querySelectorAll('a, button')).forEach(el => {
                    const t = (el.textContent || '').trim().toLowerCase();
                    if (t.includes('add new category')) el.remove();
                });

                const catList = catBox.querySelector('.cl');
                if (!catList || !categories.length) return;

                const searchInput = catBox.querySelector('input[placeholder*="Search categories"]');
                const tabLinks = Array.from(catBox.querySelectorAll('a, button'))
                    .filter(el => {
                        const t = (el.textContent || '').trim().toLowerCase();
                        return t.includes('all categories') || t.includes('most used');
                    });
                if (tabLinks.length) {
                    const tabWrap = tabLinks[0].closest('div, nav, .tabs') || null;
                    if (tabWrap) tabWrap.classList.add('cat-tabs');
                }
                const allTab = tabLinks.find(el => (el.textContent || '').toLowerCase().includes('all categories')) || null;
                const mostUsedTab = tabLinks.find(el => (el.textContent || '').toLowerCase().includes('most used')) || null;

                let mode = 'all';
                let query = '';
                const selectedIds = new Set();
                // Preserve any pre-checked categories from template state.
                Array.from(catList.querySelectorAll('input[type="checkbox"][data-id]:checked')).forEach(cb => {
                    selectedIds.add(String(cb.getAttribute('data-id')));
                });

                function renderCategories() {
                    const rows = categories
                        .filter(c => {
                            const matchQuery = !query || (c.name || '').toLowerCase().includes(query);
                            const matchMode = mode === 'all' ? true : selectedIds.has(String(c.id));
                            return matchQuery && matchMode;
                        })
                        .map(c => {
                            const checked = selectedIds.has(String(c.id)) ? 'checked' : '';
                            const depth = Number.isFinite(Number(c.depth)) ? Number(c.depth) : 0;
                            return `<label class="ci" data-depth="${depth}"><input type="checkbox" data-id="${c.id}" ${checked}> <span class="cat-label">${c.name}</span></label>`;
                        });
                    const emptyMsg = mode === 'used'
                        ? 'No selected categories yet.'
                        : 'No categories found.';
                    catList.innerHTML = rows.join('') || `<div class="text-muted px-2 py-2">${emptyMsg}</div>`;
                }

                function setMode(next) {
                    mode = next;
                    if (allTab) allTab.classList.toggle('active', next === 'all');
                    if (mostUsedTab) mostUsedTab.classList.toggle('active', next === 'used');
                    renderCategories();
                }

                catList.addEventListener('change', function (e) {
                    const cb = e.target.closest('input[type="checkbox"][data-id]');
                    if (!cb) return;
                    const id = String(cb.getAttribute('data-id'));
                    if (cb.checked) selectedIds.add(id);
                    else selectedIds.delete(id);
                    if (mode === 'used') renderCategories();
                });

                if (searchInput) {
                    searchInput.addEventListener('input', function () {
                        query = (this.value || '').trim().toLowerCase();
                        renderCategories();
                    });
                }

                if (allTab) {
                    allTab.addEventListener('click', function (e) {
                        e.preventDefault();
                        setMode('all');
                    });
                }
                if (mostUsedTab) {
                    mostUsedTab.addEventListener('click', function (e) {
                        e.preventDefault();
                        setMode('used');
                    });
                }

                setMode('all');
            })();

            // Variations & attributes: make color/size and attribute rows interactive.
            (function initVariationsSection() {
                const panel = document.getElementById('p-variations');
                if (!panel) return;
                panel.classList.add('v2-variation-oldlike');

                panel.innerHTML = `
                    <h5 class="v2-var-title">Product Variation Configuration</h5>
                    <div class="form-group row gutters-5">
                        <div class="col-md-3"><div class="v2-var-key">Colors</div></div>
                        <div class="col-md-8">
                            <select id="v2_colors" class="form-control v2-plain-select" data-none-selected-text="Nothing selected"></select>
                        </div>
                        <div class="col-md-1 align-content-center">
                            <label class="cibato-switch cibato-switch-blue mb-0">
                                <input id="v2_colors_active" value="1" type="checkbox"><span></span>
                            </label>
                        </div>
                    </div>
                    <div class="form-group row gutters-5">
                        <div class="col-md-3"><div class="v2-var-key">Attributes</div></div>
                        <div class="col-md-9">
                            <select id="v2_choice_attributes" class="form-control v2-plain-select" data-none-selected-text="Nothing selected" multiple data-selected-text-format="count"></select>
                        </div>
                    </div>
                    <div id="v2_chose_options_text" class="d-none">
                        <p class="v2-var-help">Choose the attributes of this product and then select values for each attribute</p>
                    </div>
                    <div id="v2_customer_choice_options" class="customer_choice_options mb-4"></div>
                    <div id="v2_sku_combination" class="sku_combination"></div>
                `;

                const colorSelect = document.getElementById('v2_colors');
                const colorsActive = document.getElementById('v2_colors_active');
                const choiceAttributes = document.getElementById('v2_choice_attributes');
                const choiceWrap = document.getElementById('v2_customer_choice_options');
                const choiceText = document.getElementById('v2_chose_options_text');
                const skuBox = document.getElementById('v2_sku_combination');
                const csrfToken = (window.CIBATO?.data?.csrf || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || @json(csrf_token()) || '');
                const varNavLink = Array.from(document.querySelectorAll('a,button'))
                    .find(el => ((el.textContent || '').trim().toLowerCase().replace(/\s+/g, ' ')).includes('variations & attributes'));
                if (varNavLink) {
                    varNavLink.style.fontWeight = '600';
                    varNavLink.style.color = '#009ef7';
                }

                colorSelect.innerHTML = `<option value="">Nothing selected</option>` + colors.map(c => `<option value="${c.code}">${c.name}</option>`).join('');
                choiceAttributes.innerHTML = `<option value="">Nothing selected</option>` + attributesList.map(a => `<option value="${a.id}">${a.name}</option>`).join('');
                // Keep visual consistency with other controls (do not show disabled gray skin).
                colorSelect.disabled = false;

                function lockVariationChoiceBorders() {
                    choiceWrap.querySelectorAll('.bootstrap-select').forEach((wrap) => {
                        wrap.style.setProperty('border', '0', 'important');
                        wrap.style.setProperty('box-shadow', 'none', 'important');
                        wrap.style.setProperty('background', 'transparent', 'important');
                        wrap.style.setProperty('padding', '0', 'important');
                    });
                }

                function updateSkuLegacy() {
                    const normalizeVariantToken = (v) => String(v || '')
                        .replace(/\\+"/g, '"')
                        .replace(/^['"]+|['"]+$/g, '')
                        .replace(/\s+/g, ' ')
                        .trim();
                    const params = new URLSearchParams();
                    params.append('_token', csrfToken);
                    params.append('name', document.querySelector('.ni')?.value || '');
                    params.append('unit_price', findInputByLabel(document.getElementById('p-pricing'), 'regular price')?.value || '0');
                    if (colorsActive.checked) {
                        params.append('colors_active', '1');
                        Array.from(colorSelect.selectedOptions)
                            .map(o => normalizeVariantToken(o.value))
                            .filter(Boolean)
                            .forEach(v => params.append('colors[]', v));
                    }
                    Array.from(choiceWrap.querySelectorAll('input[name="choice_no[]"]')).forEach(i => params.append('choice_no[]', i.value));
                    Array.from(choiceWrap.querySelectorAll('select[data-choice-id]')).forEach(sel => {
                        const id = sel.getAttribute('data-choice-id');
                        Array.from(sel.selectedOptions)
                            .map(o => normalizeVariantToken(o.value))
                            .filter(Boolean)
                            .forEach(v => params.append(`choice_options_${id}[]`, v));
                    });
                    fetch(@json(route('products.sku_combination')), {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8', 'X-CSRF-TOKEN': csrfToken },
                        body: params.toString()
                    }).then(r => r.text()).then(html => {
                        skuBox.innerHTML = html || '';
                        if (window.CIBATO?.uploader?.previewGenerate) CIBATO.uploader.previewGenerate();
                    }).catch(() => { skuBox.innerHTML = ''; });
                }

                function addChoiceOptionLegacy(attributeId, attributeName, preservedValues = []) {
                    const normalizeVariantToken = (v) => String(v || '')
                        .replace(/\\+"/g, '"')
                        .replace(/^['"]+|['"]+$/g, '')
                        .replace(/\s+/g, ' ')
                        .trim();
                    function normalizeOptionsHtml(raw) {
                        let decoded = (raw || '').trim();
                        try {
                            const parsed = JSON.parse(decoded);
                            if (typeof parsed === 'string') decoded = parsed;
                        } catch (_) {}
                        // Some responses may still be JSON-escaped after one parse.
                        if (/\\\/option>|\\u003Coption/i.test(decoded)) {
                            try {
                                const reparsed = JSON.parse(`"${decoded.replace(/"/g, '\\"')}"`);
                                if (typeof reparsed === 'string') decoded = reparsed;
                            } catch (_) {}
                        }
                        decoded = decoded.replace(/\\\//g, '/');
                        const matches = decoded.match(/<option[\s\S]*?<\/option>/gi);
                        if (!matches) return '';
                        const temp = document.createElement('select');
                        temp.innerHTML = matches.join('');
                        const seen = new Set();
                        return Array.from(temp.querySelectorAll('option')).map(opt => {
                            const cleanValue = normalizeVariantToken(opt.value || opt.textContent || '');
                            const cleanText = normalizeVariantToken(opt.textContent || cleanValue);
                            if (!cleanValue || seen.has(cleanValue.toLowerCase())) return '';
                            seen.add(cleanValue.toLowerCase());
                            return `<option value="${cleanValue.replace(/"/g, '&quot;')}">${cleanText}</option>`;
                        }).filter(Boolean).join('');
                    }

                    return fetch(@json(route('products.add-more-choice-option')), {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8', 'X-CSRF-TOKEN': csrfToken },
                        body: `_token=${encodeURIComponent(csrfToken)}&attribute_id=${encodeURIComponent(attributeId)}`
                    }).then(r => r.text()).then(raw => {
                        const optionsHtml = normalizeOptionsHtml(raw);
                        if (!optionsHtml) return;
                        choiceWrap.querySelector(`select[data-choice-id="${attributeId}"]`)?.closest('.form-group.row')?.remove();
                        const row = document.createElement('div');
                        row.className = 'form-group row';
                        row.innerHTML = `
                            <div class="col-md-3">
                                <input type="hidden" name="choice_no[]" value="${attributeId}">
                                <div class="v2-var-key">${attributeName}</div>
                            </div>
                            <div class="col-md-9">
                                <select class="form-control cibato-selectpicker v2-attribute-choice" name="choice_options_${attributeId}[]" data-choice-id="${attributeId}" multiple data-live-search="true" data-selected-text-format="count" data-container="body">${optionsHtml}</select>
                            </div>
                        `;
                        choiceWrap.appendChild(row);
                        const justAddedSelect = row.querySelector(`select[data-choice-id="${attributeId}"]`);
                        if (justAddedSelect && preservedValues.length) {
                            const preservedSet = new Set(preservedValues.map(v => normalizeVariantToken(v).toLowerCase()));
                            Array.from(justAddedSelect.options || []).forEach(opt => {
                                const clean = normalizeVariantToken(opt.value || opt.textContent || '').toLowerCase();
                                opt.selected = preservedSet.has(clean);
                            });
                        }
                        if (window.CIBATO?.plugins?.bootstrapSelect) CIBATO.plugins.bootstrapSelect('refresh');
                        lockVariationChoiceBorders();
                    }).catch(() => {});
                }

                colorsActive.addEventListener('change', function () {
                    if (!colorsActive.checked) Array.from(colorSelect.options).forEach(o => { o.selected = false; });
                    if (window.CIBATO?.plugins?.bootstrapSelect) CIBATO.plugins.bootstrapSelect('refresh');
                    updateSkuLegacy();
                });
                colorSelect.addEventListener('change', updateSkuLegacy);
                if (window.jQuery) $(colorSelect).on('changed.bs.select', updateSkuLegacy);

                choiceAttributes.addEventListener('change', function () {
                    const preservedChoiceValues = {};
                    Array.from(choiceWrap.querySelectorAll('select[data-choice-id]')).forEach(sel => {
                        const key = String(sel.getAttribute('data-choice-id') || '').trim();
                        if (!key) return;
                        preservedChoiceValues[key] = Array.from(sel.selectedOptions || [])
                            .map(o => String(o.value || '').trim())
                            .filter(Boolean);
                    });
                    choiceWrap.innerHTML = '';
                    const selected = Array.from(choiceAttributes.selectedOptions || []).filter(opt => String(opt.value || '').trim() !== '');
                    choiceText.classList.toggle('d-none', selected.length === 0);
                    const loaders = selected.map(opt => {
                        const attrId = String(opt.value || '').trim();
                        return addChoiceOptionLegacy(attrId, (opt.textContent || '').trim(), preservedChoiceValues[attrId] || []);
                    });
                    if (loaders.length) {
                        Promise.all(loaders).then(() => updateSkuLegacy()).catch(() => updateSkuLegacy());
                    } else {
                        updateSkuLegacy();
                    }
                });
                if (window.jQuery) $(choiceAttributes).on('changed.bs.select', function () {
                    const evt = new Event('change', { bubbles: true });
                    choiceAttributes.dispatchEvent(evt);
                });

                choiceWrap.addEventListener('change', function (e) {
                    if (e.target.matches('.v2-attribute-choice')) updateSkuLegacy();
                });
                if (window.jQuery) {
                    $(choiceWrap).on('changed.bs.select', '.v2-attribute-choice', updateSkuLegacy);
                    $(choiceWrap).on('shown.bs.select hidden.bs.select loaded.bs.select refreshed.bs.select', function () {
                        lockVariationChoiceBorders();
                    });
                }
                lockVariationChoiceBorders();
                if (window.jQuery) {
                    $(panel).on('show.bs.select', '.cibato-selectpicker', function () {
                        $(panel).find('.cibato-selectpicker').not(this).selectpicker('hide');
                    });
                }
            })();

            // Hydrate brand select from DB brands
            const brandBox = Array.from(document.querySelectorAll('.sb')).find(sb => (sb.querySelector('.sbhd h3')?.textContent || '').toLowerCase().includes('brand'));
            const brandSelect = brandBox ? brandBox.querySelector('select') : null;
            if (brandSelect && brands.length) {
                brandBox.classList.add('v2-brand-box');
                Array.from(brandBox.querySelectorAll('a, button')).forEach(el => {
                    const t = (el.textContent || '').trim().toLowerCase();
                    if (t.includes('add new brand')) el.remove();
                });
                brandSelect.innerHTML = `<option value="">Select brand</option>` + brands
                    .map(b => `<option value="${b.id}">${b.name}</option>`)
                    .join('');
                // Flatten legacy nested wrappers so the brand control stays single-layer.
                let wrap = brandSelect.parentElement;
                let depth = 0;
                while (wrap && depth < 5 && wrap !== brandBox) {
                    wrap.style.border = '0';
                    wrap.style.boxShadow = 'none';
                    wrap.style.background = 'transparent';
                    if (!wrap.classList.contains('sbbd') && !wrap.classList.contains('mbd')) {
                        wrap.style.padding = '0';
                    }
                    depth += 1;
                    wrap = wrap.parentElement;
                }
            }

            // Product tags section: clean add/remove behavior (button, Enter, comma)
            (function initTagsBox() {
                const tagsBox = findSidebarBoxByTitle('product tags');
                if (!tagsBox) return;
                tagsBox.classList.add('v2-tags-box');

                const input = tagsBox.querySelector('input');
                const addBtn = Array.from(tagsBox.querySelectorAll('button')).find(b => (b.textContent || '').trim().toLowerCase() === 'add');
                if (!input || !addBtn) return;

                const body = tagsBox.querySelector('.sbbd, .mbd') || tagsBox;
                const tgRow = addBtn.closest('.tg, .g, .f, .row');
                const hintNode = Array.from(tagsBox.querySelectorAll('.hint, small, p, .help-text, .lbl'))
                    .find(el => (el.textContent || '').toLowerCase().includes('separate tags'));

                let tagsWrap = tagsBox.querySelector('.tags');
                if (!tagsWrap) {
                    tagsWrap = document.createElement('div');
                    tagsWrap.className = 'tags';
                }
                function ensureTagsWrapPlacement() {
                    if (!tagsWrap || !body) return;
                    if (hintNode && hintNode.parentElement === body) {
                        hintNode.insertAdjacentElement('afterend', tagsWrap);
                    } else if (tgRow && tgRow.parentElement === body) {
                        tgRow.insertAdjacentElement('afterend', tagsWrap);
                    } else {
                        body.appendChild(tagsWrap);
                    }
                }
                ensureTagsWrapPlacement();
                // Hard cleanup: remove any pre-rendered template/demo pills from V4 HTML.
                Array.from(tagsBox.querySelectorAll('.pill')).forEach(p => p.remove());
                Array.from(tagsBox.querySelectorAll('.tags, .tg-tags, .tag-list')).forEach(w => {
                    if (w !== tagsWrap) w.remove();
                });
                tagsWrap.innerHTML = '';

                function normalizeTag(v) {
                    return (v || '').replace(/\s+/g, ' ').trim();
                }

                function existingTags() {
                    return Array.from(tagsWrap.querySelectorAll('.pill'))
                        .map(p => normalizeTag((p.textContent || '').replace('×', '')))
                        .filter(Boolean);
                }

                function addTag(value) {
                    const tag = normalizeTag(value);
                    if (!tag) return;
                    ensureTagsWrapPlacement();
                    const current = existingTags().map(t => t.toLowerCase());
                    if (current.includes(tag.toLowerCase())) return;
                    const pill = document.createElement('span');
                    pill.className = 'pill';
                    pill.innerHTML = `<span>${tag}</span><span class="x" title="Remove">×</span>`;
                    tagsWrap.appendChild(pill);
                }

                function addFromInput() {
                    const raw = input.value || '';
                    raw.split(',').forEach(part => addTag(part));
                    input.value = '';
                }

                addBtn.addEventListener('click', function (e) {
                    e.preventDefault();
                    addFromInput();
                });
                input.addEventListener('keydown', function (e) {
                    if (e.key === 'Enter' || e.key === ',') {
                        e.preventDefault();
                        addFromInput();
                    }
                });
                input.addEventListener('blur', function () {
                    if ((input.value || '').trim()) addFromInput();
                });
                tagsWrap.addEventListener('click', function (e) {
                    const x = e.target.closest('.x');
                    if (!x) return;
                    x.closest('.pill')?.remove();
                });
            })();

            // Linked products: make upsells/cross-sells/related fully selectable and deduplicated.
            (function initLinkedProductsSection() {
                const linkedPanel = document.getElementById('p-linked') || findMainBoxByTitle('linked products');
                if (!linkedPanel) return;

                const fields = Array.from(linkedPanel.querySelectorAll('.f'));
                const byLabel = (needle) => fields.find(f => (textOf(f.querySelector('.lbl')) || '').toLowerCase().includes(needle));
                const upsellField = byLabel('upsell');
                const crossField = byLabel('cross-sell');
                const relatedField = byLabel('related product');
                const targetFields = [upsellField, crossField, relatedField].filter(Boolean);
                if (!targetFields.length) return;

                const optionsHtml = linkedProducts
                    .filter(p => p && p.id && p.name)
                    .map(p => `<option value="${p.id}">#${p.id} - ${String(p.name).replace(/</g, '&lt;')}</option>`)
                    .join('');

                targetFields.forEach((field, idx) => {
                    // Remove duplicated input controls inside each linked block.
                    Array.from(field.querySelectorAll('input, textarea, select')).slice(1).forEach(el => el.remove());
                    const oldControl = field.querySelector('input, textarea, select');
                    if (oldControl) oldControl.remove();

                    const select = document.createElement('select');
                    select.className = 'form-control v2-linked-select cibato-selectpicker';
                    select.setAttribute('data-role', idx === 0 ? 'upsell' : (idx === 1 ? 'cross' : 'related'));
                    select.setAttribute('multiple', 'multiple');
                    select.setAttribute('data-live-search', 'true');
                    select.setAttribute('data-selected-text-format', 'count');
                    select.setAttribute('data-actions-box', 'true');
                    select.setAttribute('title', 'Search and select products...');
                    select.setAttribute('data-container', 'body');
                    select.innerHTML = optionsHtml;
                    field.insertBefore(select, field.querySelector('.hint') || null);

                    let meta = field.querySelector('.v2-linked-meta');
                    if (!meta) {
                        meta = document.createElement('div');
                        meta.className = 'v2-linked-meta';
                        field.appendChild(meta);
                    }
                    const renderMeta = () => {
                        const c = Array.from(select.selectedOptions || []).filter(o => String(o.value || '').trim() !== '').length;
                        meta.textContent = c ? `${c} product selected` : 'No product selected';
                    };
                    select.addEventListener('change', renderMeta);
                    renderMeta();
                });

                // Linked selects use the same enhanced picker + global style lock.
                const linkedSelects = Array.from(linkedPanel.querySelectorAll('select.v2-linked-select'));
                linkedSelects.forEach((sel) => {
                    sel.classList.add('v2-linked-select-locked');
                });
                if (window.jQuery && window.jQuery.fn && window.jQuery.fn.selectpicker) {
                    linkedSelects.forEach(sel => {
                        const $sel = window.jQuery(sel);
                        if (!$sel.parent().hasClass('bootstrap-select')) {
                            $sel.selectpicker({
                                size: 6,
                                liveSearch: true,
                                selectedTextFormat: 'count',
                                actionsBox: true,
                                noneSelectedText: 'Search and select products...',
                                virtualScroll: false
                            });
                        } else {
                            $sel.selectpicker('refresh');
                        }
                    });
                } else if (window.CIBATO?.plugins?.bootstrapSelect) {
                    CIBATO.plugins.bootstrapSelect();
                    CIBATO.plugins.bootstrapSelect('refresh');
                }
            })();

            // Inventory section: ensure all controls are fully functional.
            (function initInventorySection() {
                const inventory = document.getElementById('p-inventory');
                if (!inventory) return;

                const skuInput = findInputByLabel(inventory, 'sku');
                const barcodeInput = findInputByLabel(inventory, 'barcode');
                const stockQtyInput = findInputByLabel(inventory, 'stock quantity');
                const lowStockInput = findInputByLabel(inventory, 'low stock warning threshold');
                const unitInput = findInputByLabel(inventory, 'unit');
                const weightInput = findInputByLabel(inventory, 'weight');
                const minQtyInput = findInputByLabel(inventory, 'min. purchase quantity');

                function slugifySku(text) {
                    return String(text || '')
                        .toUpperCase()
                        .replace(/[^A-Z0-9]+/g, '-')
                        .replace(/^-+|-+$/g, '')
                        .slice(0, 12);
                }
                function randomAlphaNum(len) {
                    const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
                    let out = '';
                    for (let i = 0; i < len; i += 1) out += chars[Math.floor(Math.random() * chars.length)];
                    return out;
                }
                function randomDigits(len) {
                    let out = '';
                    for (let i = 0; i < len; i += 1) out += String(Math.floor(Math.random() * 10));
                    return out;
                }
                function ensurePositiveNumberInput(input, fallback) {
                    if (!input) return;
                    input.addEventListener('blur', function () {
                        const raw = String(this.value || '').trim();
                        if (!raw) {
                            this.value = fallback;
                            return;
                        }
                        const n = Number(raw);
                        if (!Number.isFinite(n) || n < 0) this.value = fallback;
                    });
                }

                // Find inventory action buttons robustly by button text.
                const invButtons = Array.from(inventory.querySelectorAll('button'));
                const skuGenButtons = invButtons.filter(b => textOf(b).toLowerCase().includes('auto-gen'));
                const barcodeGenButtons = invButtons.filter(b => textOf(b).toLowerCase() === 'generate');

                // Prevent accidental duplicate controls in this panel.
                skuGenButtons.slice(1).forEach(btn => btn.remove());
                barcodeGenButtons.slice(1).forEach(btn => btn.remove());

                const skuGenBtn = skuGenButtons[0] || null;
                const barcodeGenBtn = barcodeGenButtons[0] || null;
                if (skuGenBtn) {
                    skuGenBtn.type = 'button';
                    skuGenBtn.addEventListener('click', function (e) {
                        e.preventDefault();
                        if (!skuInput) return;
                        const baseName = document.querySelector('.ni')?.value || 'PRODUCT';
                        const prefix = slugifySku(baseName) || 'PRODUCT';
                        skuInput.value = `${prefix}-${randomAlphaNum(4)}`;
                    });
                }
                if (barcodeGenBtn) {
                    barcodeGenBtn.type = 'button';
                    barcodeGenBtn.addEventListener('click', function (e) {
                        e.preventDefault();
                        if (!barcodeInput) return;
                        barcodeInput.value = randomDigits(12);
                    });
                }

                // Stock visibility radios: keep one default checked if user unchecks all.
                const visibilityRadios = Array.from(inventory.querySelectorAll('input[type="radio"]'));
                if (visibilityRadios.length) {
                    const hasChecked = visibilityRadios.some(r => r.checked);
                    if (!hasChecked) visibilityRadios[0].checked = true;
                    visibilityRadios.forEach(r => {
                        r.addEventListener('change', function () {
                            if (!visibilityRadios.some(x => x.checked)) visibilityRadios[0].checked = true;
                        });
                    });
                }

                // Numeric guardrails for inventory values.
                ensurePositiveNumberInput(stockQtyInput, '0');
                ensurePositiveNumberInput(lowStockInput, '1');
                ensurePositiveNumberInput(minQtyInput, '1');
                if (weightInput) {
                    weightInput.addEventListener('blur', function () {
                        const raw = String(this.value || '').trim();
                        if (!raw) return;
                        const n = Number(raw);
                        if (!Number.isFinite(n) || n < 0) this.value = '';
                    });
                }
                if (unitInput) {
                    unitInput.addEventListener('blur', function () {
                        this.value = String(this.value || '').replace(/\s+/g, ' ').trim();
                    });
                }
            })();

            // Product name + permalink: constrain outer card width (embedded markup varies)
            (function markProductTitleBlockRoot() {
                const ni = document.querySelector('.ni');
                const pl = document.querySelector('.plnk');
                if (!ni || !pl) return;
                const mbox = ni.closest('.mbox');
                const sb = ni.closest('.sb');
                const shell =
                    (mbox && mbox.contains(pl)) ? mbox : (sb && sb.contains(pl)) ? sb : null;
                if (shell) {
                    shell.classList.add('v2-product-title-block-root');
                    return;
                }
                for (let el = ni.parentElement; el && el !== document.body; el = el.parentElement) {
                    if (el.contains(pl)) {
                        el.classList.add('v2-product-title-block-root');
                        break;
                    }
                }
            })();

            // Product name + permalink slug section bindings
            const nameInput = document.querySelector('.ni');
            const slugChip = document.querySelector('.plslug');
            const permalinkEditLink = Array.from(document.querySelectorAll('.plnk a')).find(a => (a.textContent || '').trim().toLowerCase() === 'edit');
            let slugManuallyEdited = false;
            const slugify = (text) => (text || '')
                .toString()
                .toLowerCase()
                .trim()
                .replace(/[^a-z0-9\s-]/g, '')
                .replace(/\s+/g, '-')
                .replace(/-+/g, '-')
                .replace(/^-|-$/g, '');

            if (slugChip) {
                slugChip.contentEditable = 'true';
                slugChip.addEventListener('input', function () {
                    slugManuallyEdited = true;
                    this.textContent = slugify(this.textContent);
                });
                slugChip.addEventListener('blur', function () {
                    if (!this.textContent.trim()) {
                        this.textContent = slugify(nameInput?.value || 'product-name') || 'product-name';
                    }
                });
            }
            if (nameInput && slugChip) {
                nameInput.addEventListener('input', function () {
                    if (!slugManuallyEdited) {
                        slugChip.textContent = slugify(this.value) || 'product-name';
                    }
                    updatePermalinkView();
                });
            }
            if (permalinkEditLink && slugChip) {
                permalinkEditLink.addEventListener('click', function (e) {
                    e.preventDefault();
                    slugManuallyEdited = true;
                    slugChip.focus();
                    document.execCommand && document.execCommand('selectAll', false, null);
                });
            }
            if (slugChip) {
                slugChip.addEventListener('input', updatePermalinkView);
                slugChip.addEventListener('blur', updatePermalinkView);
            }

            function updatePermalinkView() {
                const slug = (slugChip?.textContent || 'product-name').trim() || 'product-name';
                const origin = window.location.origin || '';
                const baseProductUrl = `${origin}/product/`;
                const permalinkLinks = Array.from(document.querySelectorAll('.plnk a'));
                const productBaseLink = permalinkLinks.find(a => (a.textContent || '').includes('/product/'));
                if (productBaseLink) {
                    productBaseLink.textContent = baseProductUrl;
                    productBaseLink.href = `${baseProductUrl}${slug}`;
                }

                const seoUrl = document.querySelector('.seo-url');
                if (seoUrl) {
                    seoUrl.textContent = `${origin.replace(/^https?:\/\//, '')}/product/${slug}`;
                }
            }
            updatePermalinkView();

            // Publish box cleanup: remove static rows not needed in V2
            const publishBox = findSidebarBoxByTitle('publish');
            if (publishBox) {
                Array.from(publishBox.querySelectorAll('.pr')).forEach(row => {
                    const txt = (row.textContent || '').toLowerCase();
                    if (txt.includes('visibility') || txt.includes('publish:') || txt.includes('status:')) {
                        row.remove();
                    }
                });
            }
            // Move Product categories box right after Publish box.
            const categoriesBoxAfterPublish = findSidebarBoxByTitle('product categories');
            if (publishBox && categoriesBoxAfterPublish && publishBox.parentElement) {
                publishBox.parentElement.insertBefore(categoriesBoxAfterPublish, publishBox.nextSibling);
            }

            function initV2Summernote(selector, height, onChangeCb) {
                if (!window.jQuery || !$(selector).length || typeof $(selector).summernote !== 'function') return;
                if ($(selector).next('.note-editor').length) return;
                $(selector).summernote({
                    height: height,
                    toolbar: [
                        ['style', ['bold', 'italic', 'underline', 'clear']],
                        ['font', ['strikethrough', 'superscript', 'subscript']],
                        ['para', ['ul', 'ol', 'paragraph']],
                        ['insert', ['link', 'picture', 'table']],
                        ['view', ['codeview']]
                    ],
                    callbacks: {
                        onChange: function(contents) {
                            if (typeof onChangeCb === 'function') onChangeCb(contents || '');
                        }
                    }
                });
            }

            // Replace V2 mock description editor with main product real editor.
            const descPanel = document.getElementById('bd-d') || findMainBoxByTitle('product description');
            if (descPanel) {
                if (!descPanel.id) descPanel.id = 'bd-d';
                descPanel.classList.remove('v2-editor-card-main', 'v2-collapsible-section');
                descPanel.classList.add('woo', 'v2-no-outer-shell');
                let descHeader = descPanel.querySelector(':scope > .woo-hd, :scope > .mhd, :scope > .hd');
                if (!descHeader) {
                    const titleNode = descPanel.querySelector(':scope > h2, :scope > h3, :scope > h4');
                    if (titleNode) {
                        const headerWrap = document.createElement('div');
                        headerWrap.className = 'woo-hd';
                        titleNode.parentNode?.insertBefore(headerWrap, titleNode);
                        headerWrap.appendChild(titleNode);
                        descHeader = headerWrap;
                    }
                }
                if (descHeader && descHeader.classList) {
                    descHeader.classList.remove('v2-section-header-with-icon');
                    descHeader.classList.add('woo-hd');
                }
                let descBody = descPanel.querySelector(':scope > .woo-body, :scope > .mbd, :scope > .bd, :scope > .content');
                if (!descBody) {
                    descBody = document.createElement('div');
                    descBody.className = 'woo-body wpanel';
                    Array.from(descPanel.childNodes).forEach((node) => {
                        if (node !== descHeader) descBody.appendChild(node);
                    });
                    descPanel.appendChild(descBody);
                } else if (descBody !== descPanel) {
                    descBody.classList.add('woo-body', 'wpanel');
                }
                descPanel.classList.add('v2-editor-card-short');
                descPanel.closest('.mbox')?.classList.add('v2-no-outer-shell');
                const mockEditor = descPanel.querySelector('.ed');
                if (mockEditor) mockEditor.remove();
                Array.from(descPanel.querySelectorAll('button, a')).forEach(el => {
                    if ((el.textContent || '').trim().toLowerCase().includes('add media')) {
                        el.remove();
                    }
                });

                const mainEditorHost = (descBody || descPanel);
                const oldMainArea = mainEditorHost.querySelector('.ed, textarea, .ea, .note-editor, #v2_main_editor');
                if (oldMainArea) {
                    const block = oldMainArea.closest('.note-editor') || oldMainArea.closest('div') || oldMainArea;
                    block.remove();
                }
                const wrap = document.createElement('div');
                wrap.style.margin = '0';
                wrap.innerHTML = '<textarea id="v2_main_editor" class="cibato-text-editor"></textarea>';
                mainEditorHost.appendChild(wrap);

                initV2Summernote('#v2_main_editor', 240, function (html) {
                    const hidden = document.getElementById('v2_description');
                    if (hidden) hidden.value = html;
                });
            }
            // Global unified section system:
            // normalize major page sections into a single .woo/.woo-hd/.woo-body pattern.
            (function initUnifiedSectionSystem() {
                const sectionRoots = Array.from(document.querySelectorAll('.mbox, .sb'))
                    .filter(el => !el.closest('#v2RealSubmitForm'));
                const iconSvg = '<svg viewBox="0 0 24 24" role="img" aria-hidden="true"><path d="M7 10l5 5 5-5"></path></svg>';
                sectionRoots.forEach((root) => {
                    root.classList.add('v2-major-section-shell');
                    root.classList.add('woo');
                    let header = root.querySelector(':scope > .woo-hd, :scope > .mhd, :scope > .hd, :scope > .sbhd');
                    if (!header) return;
                    header.classList.add('woo-hd');
                    let body = root.querySelector(':scope > .woo-body, :scope > .mbd, :scope > .bd, :scope > .content, :scope > .sbbd');
                    if (!body) {
                        body = document.createElement('div');
                        body.className = 'woo-body';
                        Array.from(root.childNodes).forEach((node) => {
                            if (node !== header) body.appendChild(node);
                        });
                        root.appendChild(body);
                    } else {
                        body.classList.add('woo-body');
                    }
                    header.querySelectorAll('.v2-section-toggle-icon, .v2-woo-toggle-icon').forEach((icon, idx) => {
                        if (idx > 0 || icon.classList.contains('v2-section-toggle-icon')) icon.remove();
                    });
                    if (!header.querySelector('.v2-woo-toggle-icon')) {
                        const icon = document.createElement('span');
                        icon.className = 'v2-woo-toggle-icon';
                        icon.setAttribute('aria-hidden', 'true');
                        icon.innerHTML = iconSvg;
                        header.appendChild(icon);
                    }
                    header.setAttribute('role', 'button');
                    header.setAttribute('tabindex', '0');
                    header.setAttribute('aria-expanded', root.classList.contains('is-collapsed') ? 'false' : 'true');
                    if (header.dataset.v2UnifiedToggleBound === '1') return;
                    header.dataset.v2UnifiedToggleBound = '1';
                    const toggle = () => {
                        root.classList.toggle('is-collapsed');
                        header.setAttribute('aria-expanded', root.classList.contains('is-collapsed') ? 'false' : 'true');
                    };
                    header.addEventListener('click', function (e) {
                        if (e.target.closest('a,button,input,select,textarea,label,.note-btn,.dropdown-menu,.bootstrap-select')) return;
                        toggle();
                    });
                    header.addEventListener('keydown', function (e) {
                        if (e.key === 'Enter' || e.key === ' ') {
                            e.preventDefault();
                            toggle();
                        }
                    });
                });
            })();

            // Add same editor for short description section.
            const shortDescBox = findMainBoxByTitle('short description');
            if (shortDescBox) {
                shortDescBox.classList.add('v2-editor-card-short');
                const body = shortDescBox.querySelector('.mbd') || shortDescBox;
                Array.from(shortDescBox.querySelectorAll('button, a')).forEach(el => {
                    if ((el.textContent || '').trim().toLowerCase().includes('add media')) {
                        el.remove();
                    }
                });
                if (!document.getElementById('v2_short_desc_editor')) {
                    const oldArea = body.querySelector('.ed, textarea, .ea');
                    if (oldArea) oldArea.remove();
                    const wrap = document.createElement('div');
                    wrap.style.margin = '0';
                    wrap.innerHTML = '<textarea id="v2_short_desc_editor" class="cibato-text-editor"></textarea>';
                    body.appendChild(wrap);
                }
                initV2Summernote('#v2_short_desc_editor', 240, function (html) {
                    const hidden = document.getElementById('v2_short_description');
                    if (hidden) hidden.value = html;
                });
            }

            // Bind uploader trigger from V2 sidebar sections by title
            const imageBox = findSidebarBoxByTitle('product image');
            if (imageBox) {
                imageBox.querySelectorAll('.idrop').forEach(el => {
                    el.addEventListener('click', (e) => {
                        e.preventDefault();
                        openUploaderBridge('#v2_pick_thumbnail', 'image', false);
                    });
                });
                imageBox.addEventListener('click', function (e) {
                    const removeBtn = e.target.closest('.v2-remove-thumb');
                    if (!removeBtn) return;
                    e.preventDefault();
                    e.stopPropagation();
                    const input = document.querySelector('#v2_pick_thumbnail .selected-files');
                    if (input) input.value = '';
                    refreshMediaPreviews();
                });
            }

            const galleryBox = findSidebarBoxByTitle('product gallery');
            if (galleryBox) {
                const galleryDrop = galleryBox.querySelector('.idrop');
                const galleryGrid = galleryBox.querySelector('.gg');
                let dragFromIndex = -1;
                if (galleryDrop) {
                    galleryDrop.addEventListener('click', (e) => {
                        e.preventDefault();
                        openUploaderBridge('#v2_pick_gallery', 'image', true);
                    });
                }
                if (galleryGrid) {
                    galleryGrid.querySelectorAll('.gs').forEach(slot => {
                        slot.addEventListener('click', (e) => {
                            if (e.target.closest('.v2-remove-gallery')) return;
                            e.preventDefault();
                            openUploaderBridge('#v2_pick_gallery', 'image', true);
                        });
                    });
                    galleryGrid.addEventListener('click', function (e) {
                        const removeBtn = e.target.closest('.v2-remove-gallery');
                        if (!removeBtn) return;
                        e.preventDefault();
                        e.stopPropagation();
                        const index = parseInt(removeBtn.getAttribute('data-index') || '-1', 10);
                        const input = document.querySelector('#v2_pick_gallery .selected-files');
                        const ids = (input?.value || '')
                            .split(',')
                            .map(v => parseInt(v, 10))
                            .filter(Boolean);
                        if (index >= 0 && index < ids.length) {
                            ids.splice(index, 1);
                            if (input) input.value = ids.join(',');
                            refreshMediaPreviews();
                        }
                    });

                    galleryGrid.addEventListener('dragstart', function (e) {
                        const slot = e.target.closest('.gs');
                        if (!slot || !slot.hasAttribute('draggable')) return;
                        dragFromIndex = Array.from(galleryGrid.querySelectorAll('.gs')).indexOf(slot);
                        slot.classList.add('v2-dragging');
                        try { e.dataTransfer.effectAllowed = 'move'; } catch (_) {}
                    });

                    galleryGrid.addEventListener('dragend', function (e) {
                        const slot = e.target.closest('.gs');
                        if (slot) slot.classList.remove('v2-dragging');
                        dragFromIndex = -1;
                    });

                    galleryGrid.addEventListener('dragover', function (e) {
                        const slot = e.target.closest('.gs');
                        if (!slot) return;
                        e.preventDefault();
                        try { e.dataTransfer.dropEffect = 'move'; } catch (_) {}
                    });

                    galleryGrid.addEventListener('drop', function (e) {
                        const targetSlot = e.target.closest('.gs');
                        if (!targetSlot) return;
                        e.preventDefault();
                        const dragToIndex = Array.from(galleryGrid.querySelectorAll('.gs')).indexOf(targetSlot);
                        if (dragFromIndex < 0 || dragToIndex < 0 || dragFromIndex === dragToIndex) return;

                        const ids = getGalleryIds();
                        if (dragFromIndex >= ids.length || dragToIndex >= ids.length) return;

                        const [moved] = ids.splice(dragFromIndex, 1);
                        ids.splice(dragToIndex, 0, moved);
                        setGalleryIds(ids);
                        refreshMediaPreviews();
                    });
                }
            }

            const videoBox2 = Array.from(document.querySelectorAll('.sb')).find(sb => (sb.querySelector('.sbhd h3')?.textContent || '').toLowerCase().includes('product video'));
            if (videoBox2) {
                videoBox2.classList.add('v2-video-box');
                // cleanup orphan uploader blocks from previous broken placements
                document.querySelectorAll('.v2-video-upload-grid').forEach(el => {
                    if (!videoBox2.contains(el)) el.remove();
                });

                const ytInput = videoBox2.querySelector('input[placeholder*="youtube"], input[placeholder*="YouTube"]');
                if (ytInput) {
                    const area = document.createElement('textarea');
                    area.rows = 1;
                    area.className = ytInput.className || '';
                    area.classList.add('v2-video-links-compact');
                    area.placeholder = ytInput.placeholder || 'https://youtube.com/...';
                    area.value = ytInput.value || '';
                    ytInput.replaceWith(area);
                }

                const browseBtns = videoBox2.querySelectorAll('button.gbtn');
                const oldUploadRow = browseBtns[0]?.closest('.g, .f, .row') || null;
                const oldThumbRow = browseBtns[1]?.closest('.g, .f, .row') || null;
                if (oldUploadRow) oldUploadRow.style.display = 'none';
                if (oldThumbRow) oldThumbRow.style.display = 'none';
                Array.from(videoBox2.querySelectorAll('label, .lbl, p, span')).forEach(el => {
                    const t = (el.textContent || '').trim().toLowerCase();
                    if (t === 'video thumbnail (optional)' || t === 'or upload video file' || t.includes('under 30 s recommended')) {
                        const wrap = el.closest('.g, .f, .row, .mbd, .sbbd');
                        if (wrap && wrap !== videoBox2) {
                            wrap.remove();
                        } else {
                            el.remove();
                        }
                    }
                });

                if (!videoBox2.querySelector('.v2-video-upload-grid')) {
                    const uploadGrid = document.createElement('div');
                    uploadGrid.className = 'v2-video-upload-grid';
                    uploadGrid.innerHTML = `
                        <button type="button" class="v2-video-upload-card" data-kind="video"><span>Upload video file</span><small>MP4 / MOV</small></button>
                        <button type="button" class="v2-video-upload-card" data-kind="thumb"><span>Upload custom thumbnail</span><small>500 × 500 px · JPG or PNG</small></button>
                    `;
                    const videoBody = videoBox2.querySelector('.sbbd, .sbbd, .mbd') || videoBox2;
                    videoBody.appendChild(uploadGrid);
                }

                if (browseBtns[0]) browseBtns[0].addEventListener('click', (e) => {
                    e.preventDefault();
                    openUploaderBridge('#v2_pick_video', 'video', true);
                });
                if (browseBtns[1]) browseBtns[1].addEventListener('click', (e) => {
                    e.preventDefault();
                    openUploaderBridge('#v2_pick_video_thumb', 'image', false);
                });

                videoBox2.addEventListener('click', function (e) {
                    const miniRemove = e.target.closest('.v2-video-mini-remove');
                    if (miniRemove) {
                        e.preventDefault();
                        e.stopPropagation();
                        const kind = miniRemove.getAttribute('data-kind');
                        if (kind === 'video-remove') setVideoIds([]);
                        if (kind === 'thumb-remove') setVideoThumbId('');
                        refreshVideoMediaPreview();
                        return;
                    }
                    const uploadCard = e.target.closest('.v2-video-upload-card');
                    if (uploadCard) {
                        e.preventDefault();
                        const kind = uploadCard.getAttribute('data-kind');
                        if (kind === 'video') openUploaderBridge('#v2_pick_video', 'video', true);
                        if (kind === 'thumb') openUploaderBridge('#v2_pick_video_thumb', 'image', false);
                        return;
                    }
                });
            }

            // Remove any stray duplicate uploader buttons rendered outside video box
            document.querySelectorAll('button.v2-video-upload-card').forEach(btn => {
                const parentGrid = btn.closest('.v2-video-upload-grid');
                if (!parentGrid) return;
                const inVideoBox = !!btn.closest('.sb') && (btn.closest('.sb').querySelector('.sbhd h3')?.textContent || '').toLowerCase().includes('product video');
                if (!inVideoBox) {
                    parentGrid.remove();
                }
            });

            const pdfBox = document.getElementById('hd-pdf')?.closest('.mbox');
            if (pdfBox) {
                pdfBox.classList.add('v2-pdf-box');
                const originalBrowse = pdfBox.querySelector('button.gbtn');
                const row = originalBrowse ? originalBrowse.closest('.g, .f, .row') : null;
                if (row && !pdfBox.querySelector('.v2-pdf-upload-row')) {
                    row.style.display = 'none';
                    const customRow = document.createElement('div');
                    customRow.className = 'v2-pdf-upload-row';
                    customRow.innerHTML = `<div class="v2-pdf-file-field">No file chosen</div><button type="button" class="v2-pdf-browse-btn">Browse</button>`;
                    row.insertAdjacentElement('afterend', customRow);
                }
                const browseBtn = pdfBox.querySelector('.v2-pdf-browse-btn');
                if (browseBtn) {
                    browseBtn.addEventListener('click', (e) => {
                        e.preventDefault();
                        openUploaderBridge('#v2_pick_pdf', 'document', false);
                        setTimeout(refreshPdfPreview, 120);
                    });
                }
                pdfBox.addEventListener('click', function (e) {
                    const rm = e.target.closest('.v2-pdf-remove');
                    if (!rm) return;
                    e.preventDefault();
                    const input = document.querySelector('#v2_pick_pdf .selected-files');
                    if (input) input.value = '';
                    refreshPdfPreview();
                });
                refreshPdfPreview();
            }

            const seoBox2 = document.getElementById('bd-seo');
            if (seoBox2) {
                const metaTitleValue = findInputByLabel(seoBox2, 'meta title')?.value || '';
                const metaDescValue = findInputByLabel(seoBox2, 'meta description')?.value || '';
                const keywordSource = findInputByLabel(seoBox2, 'keyword')?.value || '';
                const seoBody = seoBox2.querySelector('.woo-body, .mbd, .bd, .content') || seoBox2;
                const selectedMetaId = (document.querySelector('#v2_pick_meta_img .selected-files')?.value || '').trim();

                seoBody.innerHTML = `
                    <div class="f">
                        <label class="lbl">Meta Title</label>
                        <input type="text" class="form-control" id="v2_seo_meta_title" value="${String(metaTitleValue).replace(/"/g, '&quot;')}" placeholder="Meta Title">
                    </div>
                    <div class="f">
                        <label class="lbl">Description</label>
                        <textarea class="form-control" id="v2_seo_meta_description" rows="8" placeholder="Description">${String(metaDescValue || '').replace(/</g, '&lt;')}</textarea>
                    </div>
                    <div class="f">
                        <label class="lbl">Meta Image</label>
                        <div class="input-group v2-seo-meta-upload" style="cursor:pointer;">
                            <div class="input-group-prepend">
                                <div class="input-group-text bg-soft-secondary font-weight-medium">Browse</div>
                            </div>
                            <div class="form-control file-amount">${selectedMetaId ? 'File selected' : 'Choose File'}</div>
                        </div>
                        <div class="file-preview box sm v2-seo-old-preview"></div>
                    </div>
                    <div class="f">
                        <label class="lbl">Tags</label>
                        <input type="text" class="form-control" id="v2_seo_tags_input" value="${String(keywordSource).replace(/"/g, '&quot;')}" placeholder="Type and hit enter to add a tag">
                    </div>
                `;

                const uploadRow = seoBody.querySelector('.v2-seo-meta-upload');
                if (uploadRow) {
                    uploadRow.addEventListener('click', function (e) {
                        e.preventDefault();
                        openUploaderBridge('#v2_pick_meta_img', 'image', false);
                    });
                }
                refreshSeoMediaPreview();
            }

            // Shipping section: dedupe controls + make notes UX fully functional.
            (function initShippingNotes() {
                const shippingPanel = document.getElementById('p-shipping');
                if (!shippingPanel) return;
                // Remove global shipping info notice from V2 UI (informational only).
                Array.from(shippingPanel.querySelectorAll('div, p, small, span, a')).forEach((el) => {
                    const t = (textOf(el) || '').toLowerCase().replace(/\s+/g, ' ').trim();
                    if (!t) return;
                    if (t.includes('shipping is configured globally') || t.includes('shipping settings')) {
                        const wrap = el.closest('.notice, .info-notice, .alert, .f, .row, .sec, .sct, div') || el;
                        if (wrap && wrap !== shippingPanel) wrap.remove();
                        else el.remove();
                    }
                });
                // Guard against accidental duplicate controls.
                (function dedupeShippingControls() {
                    const seen = new Set();
                    Array.from(shippingPanel.querySelectorAll('label.ro, label.co, label.lbl')).forEach(lbl => {
                        const key = (textOf(lbl) || '').toLowerCase().replace(/\s+/g, ' ').trim();
                        if (!key) return;
                        if (seen.has(key)) {
                            const wrap = lbl.closest('.f, .row, .rg, div') || lbl;
                            if (wrap && wrap !== shippingPanel) wrap.remove();
                            else lbl.remove();
                            return;
                        }
                        seen.add(key);
                    });
                })();

                const deliveryInput = findInputByLabel(shippingPanel, 'delivery time text');
                const showEstimatedToggle = checkboxInPanelByText(shippingPanel, 'show estimated delivery time');
                if (deliveryInput) {
                    deliveryInput.addEventListener('blur', function () {
                        this.value = String(this.value || '').replace(/\s+/g, ' ').trim();
                    });
                    deliveryInput.addEventListener('input', function () {
                        if (showEstimatedToggle) showEstimatedToggle.checked = !!String(this.value || '').trim();
                    });
                }

                const addNoteBtn = Array.from(shippingPanel.querySelectorAll('button'))
                    .find(b => (b.textContent || '').toLowerCase().includes('add note from preset'));
                if (!addNoteBtn) return;
                addNoteBtn.type = 'button';

                const presets = [
                    'Delivery may take longer during holidays.',
                    'Shipping fees may vary by destination area.',
                    'Large items may require separate shipment.'
                ];
                const placeholder = Array.from(shippingPanel.querySelectorAll('div, p, small, em, i'))
                    .find(el => (textOf(el) || '').toLowerCase().includes('no notes added yet'));

                function getNotesWrap() {
                    let notesWrap = shippingPanel.querySelector('.v2-shipping-notes-list');
                    if (!notesWrap) {
                        notesWrap = document.createElement('div');
                        notesWrap.className = 'v2-shipping-notes-list';
                        notesWrap.style.marginTop = '10px';
                        notesWrap.style.fontSize = '12px';
                        notesWrap.style.color = '#334155';
                        addNoteBtn.insertAdjacentElement('afterend', notesWrap);
                    }
                    return notesWrap;
                }

                addNoteBtn.addEventListener('click', function (e) {
                    e.preventDefault();
                    const notesWrap = getNotesWrap();
                    const existingTexts = Array.from(notesWrap.querySelectorAll('.v2-note-item .t'))
                        .map(el => textOf(el).toLowerCase());
                    const next = presets.find(p => !existingTexts.includes(p.toLowerCase()))
                        || presets[notesWrap.querySelectorAll('.v2-note-item').length % presets.length];
                    const item = document.createElement('div');
                    item.className = 'v2-note-item';
                    item.style.marginTop = '6px';
                    item.style.display = 'flex';
                    item.style.alignItems = 'center';
                    item.style.gap = '8px';
                    item.innerHTML = `<span class="t">• ${next}</span><button type="button" class="v2-note-remove" style="border:0;background:transparent;color:#94a3b8;cursor:pointer;font-size:14px;line-height:1;">×</button>`;
                    notesWrap.appendChild(item);
                    if (placeholder) placeholder.style.display = 'none';

                    // Keep toggle aligned when notes are actively added.
                    const noteToggle = checkboxInPanelByText(shippingPanel, 'show in the shipping information');
                    if (noteToggle) noteToggle.checked = true;
                });

                shippingPanel.addEventListener('click', function (e) {
                    const rm = e.target.closest('.v2-note-remove');
                    if (!rm) return;
                    const item = rm.closest('.v2-note-item');
                    if (item) item.remove();
                    const notesWrap = shippingPanel.querySelector('.v2-shipping-notes-list');
                    if (notesWrap && !notesWrap.querySelector('.v2-note-item') && placeholder) {
                        placeholder.style.display = '';
                    }
                });
            })();

            // Warranty & COD preset note buttons (V2 UX interaction)
            (function initWarrantyCodNotes() {
                const panel = document.getElementById('p-warranty');
                if (!panel) return;
                // Remove accidental duplicate checklist rows by label text.
                (function dedupeWarrantyControls() {
                    const seen = new Set();
                    Array.from(panel.querySelectorAll('label.co, label.lbl')).forEach(lbl => {
                        const key = (textOf(lbl) || '').toLowerCase().replace(/\s+/g, ' ').trim();
                        if (!key) return;
                        if (seen.has(key)) {
                            lbl.remove();
                            return;
                        }
                        seen.add(key);
                    });
                })();

                // Ensure switches are clickable even if template script misses binding.
                Array.from(panel.querySelectorAll('.sw')).forEach(sw => {
                    if (sw.dataset.v2Bound === '1') return;
                    sw.dataset.v2Bound = '1';
                    sw.setAttribute('role', 'switch');
                });

                const addWarrantyBtns = Array.from(panel.querySelectorAll('button'))
                    .filter(b => (b.textContent || '').toLowerCase().includes('add warranty note'));
                const addCodBtns = Array.from(panel.querySelectorAll('button'))
                    .filter(b => (b.textContent || '').toLowerCase().includes('add cod note'));
                addWarrantyBtns.slice(1).forEach(b => b.remove());
                addCodBtns.slice(1).forEach(b => b.remove());
                const addWarrantyBtn = addWarrantyBtns[0] || null;
                const addCodBtn = addCodBtns[0] || null;
                if (addWarrantyBtn) addWarrantyBtn.type = 'button';
                if (addCodBtn) addCodBtn.type = 'button';

                const warrantyPlaceholder = Array.from(panel.querySelectorAll('div, p, small, em, i'))
                    .find(el => (textOf(el) || '').toLowerCase().includes('no warranty notes added yet'));
                const codPlaceholder = Array.from(panel.querySelectorAll('div, p, small, em, i'))
                    .find(el => (textOf(el) || '').toLowerCase().includes('no cod notes added yet'));

                function getNotesWrap(kind, anchorBtn) {
                    let wrap = panel.querySelector(`.v2-${kind}-notes-list`);
                    if (!wrap) {
                        wrap = document.createElement('div');
                        wrap.className = `v2-${kind}-notes-list`;
                        if (anchorBtn) anchorBtn.insertAdjacentElement('afterend', wrap);
                    }
                    return wrap;
                }
                function escapeAttr(value) {
                    return String(value || '')
                        .replace(/&/g, '&amp;')
                        .replace(/"/g, '&quot;')
                        .replace(/</g, '&lt;')
                        .replace(/>/g, '&gt;');
                }
                function addNote(kind, text, anchorBtn) {
                    const wrap = getNotesWrap(kind, anchorBtn);
                    const hasSame = Array.from(wrap.querySelectorAll('.v2-note-edit'))
                        .some(i => String(i.value || '').trim().toLowerCase() === text.toLowerCase());
                    if (hasSame) return;
                    const item = document.createElement('div');
                    item.className = 'v2-note-item';
                    item.innerHTML = `<input type="text" class="v2-note-edit" value="${escapeAttr(text)}" /><button type="button" class="v2-note-remove" data-kind="${kind}" title="Remove">×</button>`;
                    wrap.appendChild(item);
                    const ph = kind === 'warranty' ? warrantyPlaceholder : codPlaceholder;
                    if (ph) ph.style.display = 'none';
                }

                // Inject warranty period control if missing, bind with has_warranty switch.
                const hasWarrantySwitch = document.getElementById('sw-w');
                const codSwitch = document.getElementById('sw-cod');
                let warrantyPeriodInput = findInputByLabel(panel, 'warranty period');
                if (!warrantyPeriodInput) {
                    const firstDivider = panel.querySelector('.dv');
                    const field = document.createElement('div');
                    field.className = 'f v2-warranty-period-field';
                    field.innerHTML = `
                        <label class="lbl">Warranty period</label>
                        <select class="form-control v2-warranty-plain-select cibato-selectpicker" data-container="body" data-live-search="true" title="Select warranty period">
                            <option value="">Select warranty period</option>
                            ${warrantyOptions.map(w => `<option value="${w.id}">${(w.text || '').replace(/</g, '&lt;')}</option>`).join('')}
                        </select>
                    `;
                    if (firstDivider) firstDivider.insertAdjacentElement('beforebegin', field);
                    else panel.appendChild(field);
                    warrantyPeriodInput = field.querySelector('select');
                }
                if (warrantyPeriodInput) {
                    warrantyPeriodInput.classList.add('v2-warranty-plain-select', 'cibato-selectpicker');
                    warrantyPeriodInput.setAttribute('data-container', 'body');
                    warrantyPeriodInput.setAttribute('data-live-search', 'true');
                    warrantyPeriodInput.setAttribute('title', 'Select warranty period');
                    if (window.jQuery && window.jQuery.fn && window.jQuery.fn.selectpicker) {
                        const $warranty = window.jQuery(warrantyPeriodInput);
                        if (!$warranty.parent().hasClass('bootstrap-select')) {
                            $warranty.selectpicker({
                                size: 6,
                                noneSelectedText: 'Select warranty period',
                                virtualScroll: false
                            });
                        } else {
                            $warranty.selectpicker('refresh');
                        }
                    } else if (window.CIBATO?.plugins?.bootstrapSelect) {
                        CIBATO.plugins.bootstrapSelect();
                        CIBATO.plugins.bootstrapSelect('refresh');
                    }
                }
                function syncWarrantyPeriodAvailability() {
                    if (!warrantyPeriodInput) return;
                    const enabled = !!hasWarrantySwitch?.classList.contains('on');
                    warrantyPeriodInput.disabled = !enabled;
                    warrantyPeriodInput.required = enabled;
                    if (!enabled) warrantyPeriodInput.value = '';
                    if (window.jQuery && window.jQuery.fn && window.jQuery.fn.selectpicker) {
                        const $warranty = window.jQuery(warrantyPeriodInput);
                        if ($warranty.parent().hasClass('bootstrap-select')) $warranty.selectpicker('refresh');
                    }
                }
                if (hasWarrantySwitch) hasWarrantySwitch.addEventListener('click', () => setTimeout(syncWarrantyPeriodAvailability, 0));
                syncWarrantyPeriodAvailability();

                const warrantyNotesToggle = checkboxInPanelByText(panel, 'show warranty notes');
                const codNotesToggle = checkboxInPanelByText(panel, 'show cod notes');
                function syncWarrantyCodDependencies() {
                    const warrantyOn = !!hasWarrantySwitch?.classList.contains('on');
                    const codOn = !!codSwitch?.classList.contains('on');
                    if (warrantyNotesToggle && !warrantyOn) warrantyNotesToggle.checked = false;
                    if (codNotesToggle && !codOn) codNotesToggle.checked = false;
                }
                if (hasWarrantySwitch) hasWarrantySwitch.addEventListener('click', () => setTimeout(syncWarrantyCodDependencies, 0));
                if (codSwitch) codSwitch.addEventListener('click', () => setTimeout(syncWarrantyCodDependencies, 0));
                if (warrantyNotesToggle && hasWarrantySwitch) {
                    warrantyNotesToggle.addEventListener('change', function () {
                        if (this.checked) hasWarrantySwitch.classList.add('on');
                        syncWarrantyPeriodAvailability();
                    });
                }
                if (codNotesToggle && codSwitch) {
                    codNotesToggle.addEventListener('change', function () {
                        if (this.checked) codSwitch.classList.add('on');
                    });
                }
                syncWarrantyCodDependencies();

                if (addWarrantyBtn) {
                    addWarrantyBtn.addEventListener('click', function (e) {
                        e.preventDefault();
                        if (hasWarrantySwitch) hasWarrantySwitch.classList.add('on');
                        syncWarrantyPeriodAvailability();
                        addNote('warranty', 'Warranty policy details will be shown on product page.', addWarrantyBtn);
                        if (warrantyNotesToggle) warrantyNotesToggle.checked = true;
                    });
                }
                if (addCodBtn) {
                    addCodBtn.addEventListener('click', function (e) {
                        e.preventDefault();
                        if (codSwitch) codSwitch.classList.add('on');
                        addNote('cod', 'COD may vary by area and order value.', addCodBtn);
                        if (codNotesToggle) codNotesToggle.checked = true;
                    });
                }

                panel.addEventListener('click', function (e) {
                    const rm = e.target.closest('.v2-note-remove');
                    if (!rm) return;
                    const kind = rm.getAttribute('data-kind') || '';
                    rm.closest('.v2-note-item')?.remove();
                    const wrap = panel.querySelector(`.v2-${kind}-notes-list`);
                    if (wrap && !wrap.querySelector('.v2-note-item')) {
                        const ph = kind === 'warranty' ? warrantyPlaceholder : codPlaceholder;
                        if (ph) ph.style.display = '';
                    }
                });
                // Runtime harmonizer: remove wrapper artifacts only; border/focus handled by final CSS lock.
                const lockWarrantyBorders = () => {
                    panel.querySelectorAll('.bootstrap-select').forEach((wrap) => {
                        wrap.style.setProperty('border', '0', 'important');
                        wrap.style.setProperty('box-shadow', 'none', 'important');
                        wrap.style.setProperty('background', 'transparent', 'important');
                    });
                };
                lockWarrantyBorders();
                setTimeout(lockWarrantyBorders, 0);
                setTimeout(lockWarrantyBorders, 120);
                setTimeout(lockWarrantyBorders, 360);
                panel.addEventListener('click', function (e) {
                    if (e.target.closest('.bootstrap-select, .dropdown-toggle, .gbtn, .sw, .co, .dropdown-menu')) {
                        setTimeout(lockWarrantyBorders, 0);
                    }
                });
                if (window.jQuery) {
                    $(panel).on('shown.bs.select hidden.bs.select loaded.bs.select refreshed.bs.select', function () {
                        lockWarrantyBorders();
                    });
                }
            })();

            // initial paint
            refreshMediaPreviews();
            refreshVideoMediaPreview();

            // Hydrate campaign select from DB flash deals
            const pricing = document.getElementById('p-pricing');
            const campaignSelect = findInputByLabel(pricing, 'campaign');
            if (campaignSelect) {
                campaignSelect.classList.add('v2-campaign-select-input');
                campaignSelect.innerHTML = `<option value="">— None —</option>` + flashDeals
                    .map(f => `<option value="${f.id}">${f.title}</option>`)
                    .join('');
            }
            const campaignDiscountFieldForStyle = findFieldByLabel(pricing, 'campaign discount');
            const campaignDiscountTypeSelectForStyle = campaignDiscountFieldForStyle?.querySelector('select');
            campaignDiscountTypeSelectForStyle?.classList.add('v2-campaign-discount-type-input');
            // Product data box: add visible toggle affordance icon in header.
            (function initProductDataToggle() {
                const woo = Array.from(document.querySelectorAll('.woo')).find((el) => {
                    const hdText = (el.querySelector('.woo-hd h2, .woo-hd h3, .woo-hd h4')?.textContent || '').toLowerCase();
                    return hdText.includes('product data') || !!el.querySelector('.wtabs');
                });
                if (!woo) return;
                woo.classList.add('v2-layout-tabs');
                const header = woo.querySelector('.woo-hd');
                const body = woo.querySelector('.woo-body');
                if (!header || !body) return;
                if (!header.querySelector('.v2-woo-toggle-icon')) {
                    const icon = document.createElement('span');
                    icon.className = 'v2-woo-toggle-icon';
                    icon.setAttribute('aria-hidden', 'true');
                    icon.innerHTML = '<svg viewBox="0 0 24 24" role="img" aria-hidden="true"><path d="M7 10l5 5 5-5"></path></svg>';
                    header.appendChild(icon);
                }
                header.setAttribute('role', 'button');
                header.setAttribute('tabindex', '0');
                header.setAttribute('aria-expanded', woo.classList.contains('is-collapsed') ? 'false' : 'true');
                const toggle = () => {
                    woo.classList.toggle('is-collapsed');
                    header.setAttribute('aria-expanded', woo.classList.contains('is-collapsed') ? 'false' : 'true');
                };
                if (header.dataset.v2ToggleBound !== '1') {
                    header.dataset.v2ToggleBound = '1';
                    header.addEventListener('click', function (e) {
                        if (e.target.closest('a,button,input,select,textarea,label')) return;
                        toggle();
                    });
                    header.addEventListener('keydown', function (e) {
                        if (e.key === 'Enter' || e.key === ' ') {
                            e.preventDefault();
                            toggle();
                        }
                    });
                }
            })();
            // Keep a single sale scheduling control: "Sale date range".
            // Remove duplicate inline "Schedule" link beside sale price.
            const salePriceField = findFieldByLabel(pricing, 'sale price');
            if (salePriceField) {
                Array.from(salePriceField.querySelectorAll('a, button')).forEach(el => {
                    const t = (textOf(el) || '').toLowerCase();
                    if (t.includes('schedule')) el.remove();
                });
            }
            // Make sale date range fully functional with the same picker behavior as old page.
            const saleDateRangeInput = findInputByLabel(pricing, 'sale date range');
            if (saleDateRangeInput) {
                saleDateRangeInput.classList.add('cibato-date-range');
                saleDateRangeInput.setAttribute('data-time-picker', 'true');
                saleDateRangeInput.setAttribute('data-past-disable', 'true');
                saleDateRangeInput.setAttribute('data-format', 'DD-MM-Y HH:mm:ss');
                saleDateRangeInput.setAttribute('data-separator', ' to ');
                saleDateRangeInput.setAttribute('autocomplete', 'off');
                if (window.CIBATO?.plugins?.dateRange) CIBATO.plugins.dateRange();
            }
            const regularPriceInput = findInputByLabel(pricing, 'regular price');
            const salePriceInput = findInputByLabel(pricing, 'sale price');
            (function rebuildFlashSaleCampaignRow() {
                if (!pricing || !campaignSelect || !campaignDiscountFieldForStyle) return;
                const campaignField = campaignSelect.closest('.f, .col, .col-md-6, .col-lg-6, .row > div');
                if (!campaignField) return;
                const baseRow = regularPriceInput?.closest('.r2, .row, .g');
                const sourceRow = campaignField.parentElement === campaignDiscountFieldForStyle.parentElement
                    ? campaignField.parentElement
                    : null;
                let flashRow = pricing.querySelector('.v2-flash-sale-row');
                if (!flashRow) {
                    flashRow = document.createElement('div');
                    flashRow.className = `${(baseRow?.className || sourceRow?.className || 'r2').trim()} v2-flash-sale-row`;
                }
                if (sourceRow && sourceRow !== flashRow) {
                    sourceRow.parentNode?.insertBefore(flashRow, sourceRow);
                } else if (!flashRow.parentNode) {
                    campaignField.parentNode?.insertBefore(flashRow, campaignField);
                }
                flashRow.appendChild(campaignField);
                flashRow.appendChild(campaignDiscountFieldForStyle);
                campaignDiscountFieldForStyle.classList.add('v2-flash-sale-discount-field');
                if (sourceRow && sourceRow !== flashRow && sourceRow.children.length === 0) sourceRow.remove();
            })();
            [regularPriceInput, salePriceInput].forEach(inp => {
                if (!inp) return;
                inp.addEventListener('input', function () {
                    refreshPricingUX();
                    if (typeof updateSkuLegacy === 'function') updateSkuLegacy();
                });
            });
            refreshPricingUX();
            // Pricing UX rule: Regular price + Sale price are primary.
            // Remove manual discount amount/type fields to avoid conflicting inputs.
            if (pricing) {
                const discountAmountField = findFieldByLabel(pricing, 'discount amount');
                const discountTypeField = findFieldByLabel(pricing, 'discount type');
                [discountAmountField, discountTypeField].forEach(field => {
                    if (!field) return;
                    const section = field.closest('.r2, .row, .group, .sct, .sec') || field;
                    if (section && section !== pricing) {
                        // If this row only contains discount controls, remove the whole row.
                        const labels = Array.from(section.querySelectorAll('.lbl')).map(l => (textOf(l).toLowerCase()));
                        const isOnlyDiscountRow = labels.length > 0 && labels.every(t => t.includes('discount'));
                        if (isOnlyDiscountRow) section.remove();
                        else field.remove();
                    } else {
                        field.remove();
                    }
                });
            }

            // Make all V2 dropdowns use the same controllable menu skin.
            function upgradeAllV2Selects() {
                const candidateSelects = Array.from(document.querySelectorAll('.mbox select, .sb select, .woo select'))
                    .filter(sel => !sel.closest('#v2RealSubmitForm') && !sel.classList.contains('v2-no-picker'));
                candidateSelects.forEach(sel => {
                    sel.classList.add('cibato-selectpicker');
                    sel.setAttribute('data-container', 'body');
                    if (sel.multiple) sel.setAttribute('data-selected-text-format', 'count');
                    if ((sel.options?.length || 0) > 8) sel.setAttribute('data-live-search', 'true');
                });
                if (window.jQuery && window.jQuery.fn && window.jQuery.fn.selectpicker) {
                    candidateSelects.forEach(sel => {
                        const $sel = window.jQuery(sel);
                        if (!$sel.parent().hasClass('bootstrap-select')) {
                            $sel.selectpicker({
                                size: 5,
                                noneSelectedText: (window.CIBATO?.local?.nothing_selected || 'Nothing selected'),
                                virtualScroll: false
                            });
                        } else {
                            $sel.selectpicker('refresh');
                        }
                    });
                } else if (window.CIBATO?.plugins?.bootstrapSelect) {
                    CIBATO.plugins.bootstrapSelect();
                    CIBATO.plugins.bootstrapSelect('refresh');
                }
            }
            upgradeAllV2Selects();
            const campaignDiscountInputForStyle = campaignDiscountFieldForStyle?.querySelector('input');
            campaignDiscountInputForStyle?.classList.add('v2-campaign-discount-input');
            // Remove external/affiliate product block from V2 pricing UI.
            if (pricing) {
                const externalUrlField = findFieldByLabel(pricing, 'external product url');
                const buttonLabelField = findFieldByLabel(pricing, 'button label');
                [externalUrlField, buttonLabelField].forEach(field => {
                    if (!field) return;
                    const section = field.closest('.sct, .sec, .group, .row, .mbd, .f');
                    if (section && section !== pricing) section.remove();
                    else field.remove();
                });
                Array.from(pricing.querySelectorAll('h4, h5, h6, p, small, label, div')).forEach(el => {
                    const txt = (el.textContent || '').toLowerCase();
                    if (txt.includes('external / affiliate product') || txt.includes('external or affiliate products')) {
                        const wrap = el.closest('.sct, .sec, .group, .row, .mbd') || el;
                        if (wrap !== pricing) wrap.remove();
                    }
                });
            }

            // Bind publish / draft buttons from v4 template
            const publishBtn = document.querySelector('.btn-pub');
            if (publishBtn) publishBtn.addEventListener('click', function (e) { e.preventDefault(); syncAndSubmit('publish'); });

            // Remove top-right duplicate "Save draft" action (keep publish box action).
            const publishSidebar = findSidebarBoxByTitle('publish');
            const topDraftBtn = Array.from(document.querySelectorAll('button, a'))
                .find(el => (textOf(el).toLowerCase().includes('save draft')) && !(publishSidebar && publishSidebar.contains(el)));
            if (topDraftBtn) topDraftBtn.remove();

            const draftBtns = Array.from(document.querySelectorAll('.btn-d'))
                .filter(btn => textOf(btn).toLowerCase().includes('save draft'));
            draftBtns.forEach(btn => btn.addEventListener('click', function (e) { e.preventDefault(); syncAndSubmit('draft'); }));

            const previewBtns = Array.from(document.querySelectorAll('.btn-d'))
                .filter(btn => textOf(btn).toLowerCase().includes('preview'));
            previewBtns.forEach(btn => btn.addEventListener('click', function (e) {
                e.preventDefault();
                const slug = (document.querySelector('.plslug')?.textContent || '').trim() || 'product-name';
                window.open(`${window.location.origin}/product/${slug}`, '_blank');
            }));

            const moveToTrashBtn = Array.from(document.querySelectorAll('button'))
                .find(btn => (btn.textContent || '').toLowerCase().includes('move to trash'));
            if (moveToTrashBtn) {
                moveToTrashBtn.addEventListener('click', function (e) {
                    e.preventDefault();
                    if (confirm('Clear all current inputs from this V2 form?')) {
                        window.location.reload();
                    }
                });
            }

            (function initV2SidebarMetaboxColumn() {
                const anchor =
                    findSidebarBoxByTitle('publish')
                    || document.querySelector('body.v2-product-create-page .sb');
                if (!anchor || anchor.closest('#v2RealSubmitForm')) return;

                const colClass = (el) => Array.from(el?.classList || []).some((c) => c === 'col' || c.startsWith('col-'));

                function resolveSidebarOuterCol() {
                    const chain = [];
                    for (let w = anchor.parentElement; w && w !== document.body; w = w.parentElement) {
                        if (colClass(w)) chain.push(w);
                    }
                    return chain.length ? chain[chain.length - 1] : null;
                }

                function resolvePrimarySplitRow(sidebarCol) {
                    let n = sidebarCol?.parentElement;
                    while (n && n !== document.body) {
                        const cols = Array.from(n.children).filter(colClass);
                        if (cols.length >= 2 && cols.includes(sidebarCol)) return n;
                        n = n.parentElement;
                    }
                    return null;
                }

                const sidebarCol = resolveSidebarOuterCol();
                if (!sidebarCol || !document.body.contains(sidebarCol)) {
                    anchor.classList.add('v2-sidebar-metabox-shell');
                    return;
                }

                sidebarCol.classList.add('v2-sidebar-metabox-column');
                const splitRow = resolvePrimarySplitRow(sidebarCol);
                if (splitRow) {
                    splitRow.classList.add('v2-product-split-layout-row');
                    Array.from(splitRow.children).forEach((ch) => {
                        if (ch === sidebarCol) return;
                        if (colClass(ch)) ch.classList.add('v2-main-editor-flex-column');
                    });
                }
            })();

            (function tagV2MainContentBlocksMaxWidth() {
                const tag = (node) => {
                    if (!node || typeof node.closest !== 'function' || !document.body.contains(node)) return;
                    if (node.closest('#v2RealSubmitForm')) return;
                    node.classList.add('v2-main-content-block');
                };
                const applyMajorSectionSpacing = () => {
                    const allMainBlocks = Array.from(document.querySelectorAll('.v2-main-content-block'))
                        .filter((node) => !node.closest('#v2RealSubmitForm'))
                        .filter((node) => !node.classList.contains('v2-product-title-block-root'))
                        .filter((node) => !node.closest('.v2-sidebar-metabox-column'));
                    allMainBlocks.forEach((node) => {
                        node.classList.add('v2-major-flow-section');
                        node.classList.remove('v2-major-flow-spaced');
                    });
                    allMainBlocks.forEach((node, idx) => {
                        if (idx > 0) node.classList.add('v2-major-flow-spaced');
                    });
                };
                /** Prefer bordered .mbox card; fallback to anchor node (#bd-d, #bd-seo, etc.). */
                const shell = (n) => {
                    if (!n || typeof n.closest !== 'function' || !document.body.contains(n)) return null;
                    return n.closest('.mbox') || n;
                };

                ['bd-d', 'bd-seo'].forEach((id) => {
                    const el = document.getElementById(id);
                    tag(shell(el) || el);
                });
                tag(document.getElementById('hd-pdf')?.closest('.mbox'));
                tag(findMainBoxByTitle('short description'));

                const productDataShell = document.querySelector('.woo.v2-layout-tabs');
                const productDataOuterShell = shell(productDataShell) || productDataShell;
                tag(productDataOuterShell);
                applyMajorSectionSpacing();
                requestAnimationFrame(applyMajorSectionSpacing);
            })();
        })();
    </script>
    <style id="v2-final-dropdown-color-lock">
        /* SINGLE GLOBAL MASTER DROPDOWN SYSTEM (Product page only) */
        html body.v2-product-create-page {
            --v2-dd-item-bg: #f5f9ff;
            --v2-dd-item-text: #0f4c81;
            --v2-dd-panel-bg: #ffffff;
            --v2-dd-panel-border: #dbe5ef;
            --v2-dd-panel-shadow: 0 10px 24px rgba(15, 23, 42, 0.08);
            --v2-dd-control-border: #d7e2ee;
            --v2-dd-focus-border: #bcdfff;
            --v2-dd-focus-ring: 0 0 0 2px rgba(188, 223, 255, 0.35);
            --v2-dd-radius: 8px;
            --v2-dd-item-radius: 6px;
            --v2-dd-height: 40px;
        }

        /* Control system */
        html body.v2-product-create-page .bootstrap-select,
        html body.v2-product-create-page .select2-container {
            border: 0 !important;
            box-shadow: none !important;
            background: transparent !important;
            padding: 0 !important;
        }
        html body.v2-product-create-page .bootstrap-select > .dropdown-toggle,
        html body.v2-product-create-page select,
        html body.v2-product-create-page input.form-control,
        html body.v2-product-create-page textarea.form-control,
        html body.v2-product-create-page input[type="text"],
        html body.v2-product-create-page input[type="number"],
        html body.v2-product-create-page input[type="email"],
        html body.v2-product-create-page input[type="url"],
        html body.v2-product-create-page input[type="search"],
        html body.v2-product-create-page input[type="tel"],
        html body.v2-product-create-page input[type="password"],
        html body.v2-product-create-page textarea,
        html body.v2-product-create-page .form-control.select2-hidden-accessible + .select2 .select2-selection--single,
        html body.v2-product-create-page .form-control.select2-hidden-accessible + .select2 .select2-selection--multiple,
        html body.v2-product-create-page .select2-container--default .select2-selection--single,
        html body.v2-product-create-page .select2-container--default .select2-selection--multiple {
            border: 1px solid var(--v2-dd-control-border) !important;
            border-radius: var(--v2-dd-radius) !important;
            min-height: var(--v2-dd-height) !important;
            box-shadow: none !important;
            outline: none !important;
            background: #ffffff !important;
        }
        html body.v2-product-create-page .bootstrap-select > .dropdown-toggle:hover,
        html body.v2-product-create-page .bootstrap-select > .dropdown-toggle:focus,
        html body.v2-product-create-page .bootstrap-select.show > .dropdown-toggle,
        html body.v2-product-create-page .bootstrap-select.open > .dropdown-toggle,
        html body.v2-product-create-page .show > .btn.dropdown-toggle,
        html body.v2-product-create-page .show > .btn-light.dropdown-toggle,
        html body.v2-product-create-page select:hover,
        html body.v2-product-create-page select:focus,
        html body.v2-product-create-page input.form-control:hover,
        html body.v2-product-create-page input.form-control:focus,
        html body.v2-product-create-page textarea.form-control:hover,
        html body.v2-product-create-page textarea.form-control:focus,
        html body.v2-product-create-page input[type="text"]:hover,
        html body.v2-product-create-page input[type="text"]:focus,
        html body.v2-product-create-page input[type="number"]:hover,
        html body.v2-product-create-page input[type="number"]:focus,
        html body.v2-product-create-page input[type="email"]:hover,
        html body.v2-product-create-page input[type="email"]:focus,
        html body.v2-product-create-page input[type="url"]:hover,
        html body.v2-product-create-page input[type="url"]:focus,
        html body.v2-product-create-page input[type="search"]:hover,
        html body.v2-product-create-page input[type="search"]:focus,
        html body.v2-product-create-page input[type="tel"]:hover,
        html body.v2-product-create-page input[type="tel"]:focus,
        html body.v2-product-create-page input[type="password"]:hover,
        html body.v2-product-create-page input[type="password"]:focus,
        html body.v2-product-create-page textarea:hover,
        html body.v2-product-create-page textarea:focus,
        html body.v2-product-create-page .select2-container--default.select2-container--focus .select2-selection--single,
        html body.v2-product-create-page .select2-container--default.select2-container--focus .select2-selection--multiple,
        html body.v2-product-create-page .select2-container--default.select2-container--open .select2-selection--single,
        html body.v2-product-create-page .select2-container--default.select2-container--open .select2-selection--multiple {
            border-color: var(--v2-dd-focus-border) !important;
            box-shadow: var(--v2-dd-focus-ring) !important;
            outline: none !important;
            background: #ffffff !important;
        }

        /* Dropdown panel system */
        html body.v2-product-create-page .bootstrap-select .dropdown-menu,
        html body.v2-product-create-page .bs-container .dropdown-menu,
        html body.v2-product-create-page .dropdown-menu,
        html body.v2-product-create-page .select2-container .select2-dropdown {
            background: var(--v2-dd-panel-bg) !important;
            border: 1px solid var(--v2-dd-panel-border) !important;
            border-radius: var(--v2-dd-radius) !important;
            box-shadow: var(--v2-dd-panel-shadow) !important;
            padding: 6px !important;
        }
        html body.v2-product-create-page .bootstrap-select .dropdown-menu .inner,
        html body.v2-product-create-page .bootstrap-select .dropdown-menu.inner,
        html body.v2-product-create-page .bs-container .dropdown-menu .inner,
        html body.v2-product-create-page .bs-container .dropdown-menu.inner {
            border: 0 !important;
            box-shadow: none !important;
            outline: 0 !important;
            background: transparent !important;
        }
        html body.v2-product-create-page .bootstrap-select .dropdown-menu li > a,
        html body.v2-product-create-page .bs-container .dropdown-menu li > a,
        html body.v2-product-create-page .bootstrap-select .dropdown-menu .dropdown-item,
        html body.v2-product-create-page .bs-container .dropdown-menu .dropdown-item,
        html body.v2-product-create-page .dropdown-menu li > a,
        html body.v2-product-create-page .dropdown-menu .dropdown-item,
        html body.v2-product-create-page .select2-container .select2-results__option {
            border-radius: var(--v2-dd-item-radius) !important;
        }

        /* Unified item interaction states: hover/selected/active/focus */
        html body.v2-product-create-page .bootstrap-select .dropdown-menu li > a:hover,
        html body.v2-product-create-page .bootstrap-select .dropdown-menu li > a:focus,
        html body.v2-product-create-page .bootstrap-select .dropdown-menu li > a:active,
        html body.v2-product-create-page .bootstrap-select .dropdown-menu li.active > a,
        html body.v2-product-create-page .bootstrap-select .dropdown-menu li.active > a:hover,
        html body.v2-product-create-page .bootstrap-select .dropdown-menu li.selected > a,
        html body.v2-product-create-page .bootstrap-select .dropdown-menu li.selected > a:hover,
        html body.v2-product-create-page .bootstrap-select .dropdown-menu li.selected.active > a,
        html body.v2-product-create-page .bootstrap-select .dropdown-menu li.selected.active > a:hover,
        html body.v2-product-create-page .bootstrap-select .dropdown-menu .dropdown-item:hover,
        html body.v2-product-create-page .bootstrap-select .dropdown-menu .dropdown-item:focus,
        html body.v2-product-create-page .bootstrap-select .dropdown-menu .dropdown-item.active,
        html body.v2-product-create-page .bootstrap-select .dropdown-menu .dropdown-item:active,
        html body.v2-product-create-page .bs-container .dropdown-menu li > a:hover,
        html body.v2-product-create-page .bs-container .dropdown-menu li > a:focus,
        html body.v2-product-create-page .bs-container .dropdown-menu li > a:active,
        html body.v2-product-create-page .bs-container .dropdown-menu li.active > a,
        html body.v2-product-create-page .bs-container .dropdown-menu li.active > a:hover,
        html body.v2-product-create-page .bs-container .dropdown-menu li.selected > a,
        html body.v2-product-create-page .bs-container .dropdown-menu li.selected > a:hover,
        html body.v2-product-create-page .bs-container .dropdown-menu li.selected.active > a,
        html body.v2-product-create-page .bs-container .dropdown-menu li.selected.active > a:hover,
        html body.v2-product-create-page .bs-container .dropdown-menu .dropdown-item:hover,
        html body.v2-product-create-page .bs-container .dropdown-menu .dropdown-item:focus,
        html body.v2-product-create-page .bs-container .dropdown-menu .dropdown-item.active,
        html body.v2-product-create-page .bs-container .dropdown-menu .dropdown-item:active,
        html body.v2-product-create-page .dropdown-menu li > a:hover,
        html body.v2-product-create-page .dropdown-menu li > a:focus,
        html body.v2-product-create-page .dropdown-menu li > a:active,
        html body.v2-product-create-page .dropdown-menu li.active > a,
        html body.v2-product-create-page .dropdown-menu li.active > a:hover,
        html body.v2-product-create-page .dropdown-menu li.selected > a,
        html body.v2-product-create-page .dropdown-menu li.selected > a:hover,
        html body.v2-product-create-page .dropdown-menu li.selected.active > a,
        html body.v2-product-create-page .dropdown-menu li.selected.active > a:hover,
        html body.v2-product-create-page .dropdown-menu .dropdown-item:hover,
        html body.v2-product-create-page .dropdown-menu .dropdown-item:focus,
        html body.v2-product-create-page .dropdown-menu .dropdown-item.active,
        html body.v2-product-create-page .dropdown-menu .dropdown-item:active,
        html body.v2-product-create-page .select2-container--default .select2-results__option--highlighted[aria-selected],
        html body.v2-product-create-page .select2-container--default .select2-results__option[aria-selected="true"] {
            background-image: none !important;
            background-color: var(--v2-dd-item-bg) !important;
            color: var(--v2-dd-item-text) !important;
        }

        /* Checkmark system */
        html body.v2-product-create-page .bootstrap-select .dropdown-menu li.selected .bs-ok-default:after,
        html body.v2-product-create-page .bootstrap-select .dropdown-menu li.selected .check-mark,
        html body.v2-product-create-page .bs-container .dropdown-menu li.selected .bs-ok-default:after,
        html body.v2-product-create-page .bs-container .dropdown-menu li.selected .check-mark {
            color: var(--v2-dd-item-text) !important;
            border-color: var(--v2-dd-item-text) !important;
        }

        /* Native select fallback */
        html body.v2-product-create-page select option:checked,
        html body.v2-product-create-page select option:hover,
        html body.v2-product-create-page select option:focus {
            background: #f5f9ff linear-gradient(0deg, #f5f9ff, #f5f9ff) !important;
            color: #0f4c81 !important;
        }
    </style>
    <style id="v2-unified-section-system">
        /* Single unified section/card/meta-box system across this page */
        html body.v2-product-create-page .woo {
            border: 1px solid #d7e3ef !important;
            border-radius: 12px !important;
            box-shadow: 0 8px 24px rgba(15, 23, 42, 0.04) !important;
            background: #fff !important;
            overflow: hidden !important;
        }
        html body.v2-product-create-page .woo > .woo-hd,
        html body.v2-product-create-page .woo > .mhd,
        html body.v2-product-create-page .woo > .hd,
        html body.v2-product-create-page .woo > .sbhd {
            background: linear-gradient(180deg, #fbfdff 0%, #f6faff 100%) !important;
            border-bottom: 1px solid #dde7f1 !important;
            min-height: 46px !important;
            padding: 11px 16px !important;
            padding-right: 42px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: flex-start !important;
            position: relative !important;
            cursor: pointer !important;
            user-select: none !important;
            margin: 0 !important;
        }
        html body.v2-product-create-page .woo > .woo-hd h2,
        html body.v2-product-create-page .woo > .woo-hd h3,
        html body.v2-product-create-page .woo > .woo-hd h4,
        html body.v2-product-create-page .woo > .mhd h2,
        html body.v2-product-create-page .woo > .mhd h3,
        html body.v2-product-create-page .woo > .mhd h4,
        html body.v2-product-create-page .woo > .hd h2,
        html body.v2-product-create-page .woo > .hd h3,
        html body.v2-product-create-page .woo > .hd h4,
        html body.v2-product-create-page .woo > .sbhd h2,
        html body.v2-product-create-page .woo > .sbhd h3,
        html body.v2-product-create-page .woo > .sbhd h4 {
            font-size: 14px !important;
            font-weight: 700 !important;
            color: #0f172a !important;
            margin: 0 !important;
            line-height: 1.2 !important;
            text-align: left !important;
        }
        html body.v2-product-create-page .woo .v2-woo-toggle-icon {
            width: 22px !important;
            height: 22px !important;
            border: 1px solid #d6e3f0 !important;
            border-radius: 999px !important;
            background: #ffffff !important;
            color: #5f738a !important;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.06) !important;
            position: absolute !important;
            right: 12px !important;
            top: 50% !important;
            margin-top: -11px !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
        }
        html body.v2-product-create-page .woo .v2-woo-toggle-icon svg {
            width: 12px !important;
            height: 12px !important;
            display: block !important;
        }
        html body.v2-product-create-page .woo .v2-woo-toggle-icon svg path {
            stroke: currentColor !important;
            stroke-width: 2.2 !important;
            stroke-linecap: round !important;
            stroke-linejoin: round !important;
            fill: none !important;
        }
        html body.v2-product-create-page .woo > .woo-hd:hover .v2-woo-toggle-icon,
        html body.v2-product-create-page .woo > .mhd:hover .v2-woo-toggle-icon,
        html body.v2-product-create-page .woo > .hd:hover .v2-woo-toggle-icon,
        html body.v2-product-create-page .woo > .sbhd:hover .v2-woo-toggle-icon {
            background: #f5f9ff !important;
            color: #0f4c81 !important;
            border-color: #bcdfff !important;
            box-shadow: 0 2px 6px rgba(15, 76, 129, 0.12) !important;
        }
        html body.v2-product-create-page .woo.is-collapsed .v2-woo-toggle-icon {
            transform: rotate(-90deg) !important;
        }
        html body.v2-product-create-page .woo.is-collapsed > .woo-body,
        html body.v2-product-create-page .woo.is-collapsed > .mbd,
        html body.v2-product-create-page .woo.is-collapsed > .bd,
        html body.v2-product-create-page .woo.is-collapsed > .content,
        html body.v2-product-create-page .woo.is-collapsed > .sbbd {
            display: none !important;
        }
        /* Product description editor area: full-width content (no side gap) */
        html body.v2-product-create-page #bd-d > .woo-body,
        html body.v2-product-create-page #bd-d > .woo-body.wpanel {
            padding: 0 !important;
            margin: 0 !important;
        }
        html body.v2-product-create-page #bd-d #v2_main_editor + .note-editor.note-frame {
            width: 100% !important;
            margin: 0 !important;
            border: 0 !important;
            box-shadow: none !important;
            outline: 0 !important;
        }
        html body.v2-product-create-page #bd-d .note-editor.note-frame {
            border: 0 !important;
            box-shadow: none !important;
            outline: 0 !important;
        }
        /* Product description: enforce single clean border (remove inner duplicate lines) */
        html body.v2-product-create-page #bd-d #v2_main_editor + .note-editor,
        html body.v2-product-create-page #bd-d #v2_main_editor + .note-editor.note-frame,
        html body.v2-product-create-page #bd-d #v2_main_editor + .note-editor.note-frame.card {
            border: 0 !important;
            box-shadow: none !important;
            outline: 0 !important;
        }
        html body.v2-product-create-page #bd-d #v2_main_editor + .note-editor.note-frame .note-editing-area,
        html body.v2-product-create-page #bd-d #v2_main_editor + .note-editor.note-frame .note-editable {
            border: 0 !important;
            box-shadow: none !important;
        }
        /* Keep bottom resize handle (like short description): do not hide .note-resizebar / .note-statusbar */
        html body.v2-product-create-page #bd-d #v2_main_editor + .note-editor.note-frame .note-resizebar {
            border-top: 1px solid #e2e8f0 !important;
            background: #f8fafc !important;
        }
        html body.v2-product-create-page #bd-d .woo,
        html body.v2-product-create-page #bd-d .mbox {
            box-shadow: none !important;
        }
        html body.v2-product-create-page .v2-editor-card-short > .woo-body,
        html body.v2-product-create-page .v2-editor-card-short > .woo-body.wpanel {
            padding: 0 !important;
            margin: 0 !important;
        }
        html body.v2-product-create-page .v2-editor-card-short #v2_short_desc_editor + .note-editor.note-frame {
            width: 100% !important;
            margin: 0 !important;
            border: 0 !important;
            box-shadow: none !important;
        }
        html body.v2-product-create-page .v2-editor-card-short.woo {
            box-shadow: none !important;
            filter: none !important;
        }
        html body.v2-product-create-page #bd-d.woo {
            box-shadow: none !important;
            filter: none !important;
            border-top-left-radius: 0 !important;
            border-top-right-radius: 0 !important;
        }
        html body.v2-product-create-page #bd-d.woo > .woo-hd,
        html body.v2-product-create-page #bd-d.woo > .mhd,
        html body.v2-product-create-page #bd-d.woo > .hd {
            border-top-left-radius: 0 !important;
            border-top-right-radius: 0 !important;
        }
        html body.v2-product-create-page .v2-no-outer-shell,
        html body.v2-product-create-page .v2-no-outer-shell.woo,
        html body.v2-product-create-page .v2-no-outer-shell.mbox {
            border: 1px solid #d7e3ef !important;
            box-shadow: none !important;
            outline: none !important;
            filter: none !important;
        }
        /* Publish box buttons: minimal style + subtle hover animation */
        html body.v2-product-create-page .sb:has(.sbhd h3) .btn-pub,
        html body.v2-product-create-page .sb:has(.sbhd h3) .btn-d,
        html body.v2-product-create-page .sb:has(.sbhd h3) button:not(.v2-video-upload-card) {
            transition: transform .18s ease, box-shadow .18s ease, background-color .18s ease, border-color .18s ease, color .18s ease !important;
            will-change: transform;
        }
        /* Publish top action buttons (Save draft / Preview) redesign */
        html body.v2-product-create-page .sb:has(.sbhd h3) .btn-d {
            height: 34px !important;
            min-width: 74px;
            border-radius: 7px !important;
            border: 1px solid #cfdceb !important;
            background: linear-gradient(180deg, #ffffff 0%, #f7fbff 100%) !important;
            color: #243b53 !important;
            font-size: 13px !important;
            font-weight: 600 !important;
            padding: 0 12px !important;
            box-shadow: none !important;
        }
        html body.v2-product-create-page .sb:has(.sbhd h3) .btn-pub {
            margin-bottom: 8px !important;
        }
        html body.v2-product-create-page .sb:has(.sbhd h3) .btn-pub:hover,
        html body.v2-product-create-page .sb:has(.sbhd h3) button:not(.v2-video-upload-card):not(.btn-d):hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 14px rgba(15, 23, 42, 0.10) !important;
        }
        html body.v2-product-create-page .sb:has(.sbhd h3) .btn-d:hover,
        html body.v2-product-create-page .sb:has(.sbhd h3) .btn-d:focus {
            transform: none;
            border-color: #bcdfff !important;
            background: linear-gradient(180deg, #fbfdff 0%, #eef6ff 100%) !important;
            color: #0f4c81 !important;
            box-shadow: none !important;
            outline: none !important;
        }
        html body.v2-product-create-page .sb:has(.sbhd h3) .btn-pub:active,
        html body.v2-product-create-page .sb:has(.sbhd h3) button:not(.v2-video-upload-card):not(.btn-d):active {
            transform: translateY(0);
            box-shadow: 0 2px 6px rgba(15, 23, 42, 0.08) !important;
        }
        html body.v2-product-create-page .sb:has(.sbhd h3) .btn-d:active {
            transform: none;
            box-shadow: none !important;
        }
        /* Brand select: no hover effect + arrow perfectly right aligned */
        html body.v2-product-create-page .v2-brand-box .bootstrap-select > .dropdown-toggle,
        html body.v2-product-create-page .v2-brand-box .bootstrap-select > .dropdown-toggle:hover,
        html body.v2-product-create-page .v2-brand-box .bootstrap-select > .dropdown-toggle:focus,
        html body.v2-product-create-page .v2-brand-box .bootstrap-select > .dropdown-toggle:active,
        html body.v2-product-create-page .v2-brand-box .bootstrap-select.show > .dropdown-toggle,
        html body.v2-product-create-page .v2-brand-box .bootstrap-select.open > .dropdown-toggle {
            background: #fff !important;
            border-color: #d7e2ee !important;
            box-shadow: none !important;
            outline: none !important;
        }
        html body.v2-product-create-page .v2-brand-box .bootstrap-select > .dropdown-toggle {
            padding-left: 12px !important;
            padding-right: 34px !important;
            padding-top: 0 !important;
            padding-bottom: 0 !important;
            position: relative !important;
            transition: none !important;
            height: 42px !important;
            min-height: 42px !important;
            line-height: 42px !important;
        }
        html body.v2-product-create-page .v2-brand-box .bootstrap-select > .dropdown-toggle .filter-option,
        html body.v2-product-create-page .v2-brand-box .bootstrap-select > .dropdown-toggle .filter-option-inner,
        html body.v2-product-create-page .v2-brand-box .bootstrap-select > .dropdown-toggle .filter-option-inner-inner {
            padding-left: 0 !important;
            padding-right: 0 !important;
            border: 0 !important;
            box-shadow: none !important;
            background: transparent !important;
            text-decoration: none !important;
            min-height: 42px !important;
        }
        html body.v2-product-create-page .v2-brand-box .bootstrap-select > .dropdown-toggle .filter-option {
            margin: 0 !important;
            width: 100% !important;
            max-width: 100% !important;
            box-sizing: border-box !important;
            padding: 0 28px 0 0 !important;
            position: static !important;
            display: flex !important;
            align-items: center !important;
            justify-content: flex-start !important;
        }
        html body.v2-product-create-page .v2-brand-box .bootstrap-select > .dropdown-toggle .filter-option-inner-inner {
            display: block !important;
            line-height: 1.2 !important;
        }
        html body.v2-product-create-page .v2-brand-box .bootstrap-select > .dropdown-toggle .filter-option::before,
        html body.v2-product-create-page .v2-brand-box .bootstrap-select > .dropdown-toggle .filter-option::after,
        html body.v2-product-create-page .v2-brand-box .bootstrap-select > .dropdown-toggle .filter-option-inner::before,
        html body.v2-product-create-page .v2-brand-box .bootstrap-select > .dropdown-toggle .filter-option-inner::after,
        html body.v2-product-create-page .v2-brand-box .bootstrap-select > .dropdown-toggle .filter-option-inner-inner::before,
        html body.v2-product-create-page .v2-brand-box .bootstrap-select > .dropdown-toggle .filter-option-inner-inner::after {
            content: none !important;
            border: 0 !important;
            box-shadow: none !important;
            background: transparent !important;
        }
        html body.v2-product-create-page .v2-brand-box .bootstrap-select > .dropdown-toggle .bs-caret,
        html body.v2-product-create-page .v2-brand-box .bootstrap-select > .dropdown-toggle .caret {
            display: none !important;
        }
        html body.v2-product-create-page .v2-brand-box .bootstrap-select,
        html body.v2-product-create-page .v2-brand-box .bootstrap-select * {
            box-shadow: none !important;
        }
        html body.v2-product-create-page .v2-brand-box .bootstrap-select > .dropdown-toggle::before {
            content: none !important;
            display: none !important;
            border: 0 !important;
            box-shadow: none !important;
            background: transparent !important;
        }
        html body.v2-product-create-page .v2-brand-box .bootstrap-select > .dropdown-toggle::after {
            content: "";
            position: absolute;
            right: 18px;
            top: 50%;
            width: 6px;
            height: 6px;
            margin-top: -4px;
            border-right: 1.7px solid #64748b;
            border-bottom: 1.7px solid #64748b;
            transform: rotate(45deg);
            pointer-events: none;
            background: transparent !important;
        }
        /* FINAL Brand field lock: simple, stable, symmetric */
        html body.v2-product-create-page .v2-brand-box .bootstrap-select {
            width: calc(100% - 24px) !important;
            margin: 6px auto !important;
            display: block !important;
        }
        html body.v2-product-create-page .v2-brand-box .bootstrap-select > .dropdown-toggle {
            height: 40px !important;
            min-height: 40px !important;
            width: 100% !important;
            padding: 0 34px 0 12px !important;
            line-height: 1.2 !important;
            border: 1px solid #d7e2ee !important;
            border-radius: 8px !important;
            background: #fff !important;
            box-shadow: none !important;
            display: flex !important;
            align-items: center !important;
            transition: none !important;
        }
        html body.v2-product-create-page .v2-brand-box .bootstrap-select > .dropdown-toggle:hover,
        html body.v2-product-create-page .v2-brand-box .bootstrap-select > .dropdown-toggle:focus,
        html body.v2-product-create-page .v2-brand-box .bootstrap-select.open > .dropdown-toggle,
        html body.v2-product-create-page .v2-brand-box .bootstrap-select.show > .dropdown-toggle {
            background: #fff !important;
            border-color: #d7e2ee !important;
            box-shadow: none !important;
            outline: none !important;
        }
        html body.v2-product-create-page .v2-brand-box .bootstrap-select > .dropdown-toggle .filter-option {
            width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
            display: flex !important;
            align-items: center !important;
            justify-content: flex-start !important;
            min-height: 40px !important;
        }
        html body.v2-product-create-page .v2-brand-box .bootstrap-select > .dropdown-toggle .filter-option-inner,
        html body.v2-product-create-page .v2-brand-box .bootstrap-select > .dropdown-toggle .filter-option-inner-inner {
            width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
            line-height: 1.2 !important;
            border: 0 !important;
            box-shadow: none !important;
            background: transparent !important;
            display: flex !important;
            align-items: center !important;
        }
        html body.v2-product-create-page .v2-brand-box .bootstrap-select > .dropdown-toggle .bs-caret,
        html body.v2-product-create-page .v2-brand-box .bootstrap-select > .dropdown-toggle .caret {
            display: none !important;
        }
        html body.v2-product-create-page .v2-brand-box .bootstrap-select > .dropdown-toggle::after {
            content: "";
            position: absolute;
            right: 14px;
            top: 50%;
            width: 6px;
            height: 6px;
            margin-top: -4px;
            border-right: 1.7px solid #64748b;
            border-bottom: 1.7px solid #64748b;
            transform: rotate(45deg);
            pointer-events: none;
        }
        html body.v2-product-create-page .v2-publish-status-row {
            min-height: 34px;
            display: flex !important;
            align-items: center !important;
            justify-content: space-between !important;
            margin: 0 !important;
            padding: 0 !important;
            line-height: 1.2;
        }
    </style>
@endsection
