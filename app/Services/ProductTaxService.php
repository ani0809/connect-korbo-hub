<?php

namespace App\Services;

class ProductTaxService
{
    public function store(array $data)
    {
        // VAT/Tax module is hard-disabled.
        return;

    }

    public function product_duplicate_store($product_taxes , $product_new)
    {
        // VAT/Tax module is hard-disabled.
        return;
    }

}
