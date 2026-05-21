<script>
    var spinner =
        '<div class="h-100 d-flex align-items-center justify-content-center">' +
        '<div class="lds-ellipsis"><div></div><div></div><div></div><div></div></div>' +
        "</div>";
        
    // top_category_products_tab("all");
    normalizeDashDashboardBlocks();
    if ($("#dash-top-categories").length) {
        dash_top_categories("all");
    }
    top_sellers_products_tab('all');
    // top_brands_products_tab('all');

    $(".top_category_products_tab").click(function () {
        top_category_products_tab($(this).data("target"));
    });

    $(".dash_top_categories").click(function () {
        dash_top_categories($(this).data("target"));
    });

    $(".top_sellers_products_tab").click(function () {
        top_sellers_products_tab($(this).data("target"));
    });

    $(".top_brands_products_tab").click(function () {
        top_brands_products_tab($(this).data("target"));
    });

    function top_category_products_tab(interval_type) {
        $("#top-category-products-section").html(spinner);
        $.ajax({
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            type: "POST",
            url:
                CIBATO.data.appUrl +
                "/admin/dashboard/top-category-products-section",
            data: {
                interval_type: interval_type,
            },
            success: function (data) {
                $("#top-category-products-section").html(data);
                CIBATO.plugins.slickCarousel();
            },
        });
    }

    function dash_top_categories(interval_type) {
        if (!$("#dash-top-categories").length) {
            return;
        }
        $("#dash-top-categories").html(spinner);
        $.ajax({
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            type: "POST",
            url: CIBATO.data.appUrl + "/admin/dashboard/store-top-categories",
            data: {
                interval_type: interval_type,
            },
            success: function (data) {
                $("#dash-top-categories").html(data);
            },
        });
    }

    function top_sellers_products_tab(interval_type) {
        $("#top-sellers-products-section").html(spinner);
        $.ajax({
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            type: "POST",
            url:
                CIBATO.data.appUrl +
                "/admin/dashboard/top-sellers-products-section",
            data: {
                interval_type: interval_type,
            },
            success: function (data) {
                $("#top-sellers-products-section").html(data);
                CIBATO.plugins.slickCarousel();
            },
        });
    }

    function top_brands_products_tab(interval_type) {
        $("#top-brands-products-section").html(spinner);
        $.ajax({
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            type: "POST",
            url:
                CIBATO.data.appUrl +
                "/admin/dashboard/top-brands-products-section",
            data: {
                interval_type: interval_type,
            },
            success: function (data) {
                $("#top-brands-products-section").html(data);
                CIBATO.plugins.slickCarousel();
            },
        });
    }

    function normalizeDashDashboardBlocks() {
        var $brandTitle = $('h2').filter(function () {
            return ($(this).text() || '').trim().toLowerCase().indexOf('top brands') !== -1;
        }).first();

        if (!$brandTitle.length) {
            return;
        }

        var $brandCol = $brandTitle.closest('.col-sm-6, .col-lg-6, .col-sm-12');
        var $categoryTitle = $('h2').filter(function () {
            return ($(this).text() || '').trim().toLowerCase().indexOf('top category') !== -1;
        }).first();
        var $categoryCol = $categoryTitle.closest('.col-sm-6, .col-lg-6, .col-sm-12');

        if ($categoryCol.length && $brandCol.length && !$brandCol.is($categoryCol)) {
            $brandCol.replaceWith($categoryCol);
        } else if ($brandCol.length) {
            $brandCol.remove();
        }
    }
</script>