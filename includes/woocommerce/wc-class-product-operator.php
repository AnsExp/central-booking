<?php

class WC_Product_Operator extends WC_Product
{
    public function __construct($product)
    {
        parent::__construct($product);
        $this->product_type = 'operator';
    }
}