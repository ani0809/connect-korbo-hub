@include('frontend.partials.unified_product_card', [
    'product' => $product,
    'card_padding_class' => ($card_padding_class ?? 'px-2'),
    'image_aspect_ratio' => ($image_aspect_ratio ?? '1 / 1'),
    'image_fill_cover' => ($image_fill_cover ?? false),
])
