<?php

class Controller
{
    static public function CheckPath($path)
    {
        $uri = explode("/", $path);
        if (!isset($uri) && $uri[1] != "products") {
            return 404;
        }
        if(array_key_exists(2, $uri)){
            return $uri[2];
        }
        return false;
    }
    static public function GetJsonAPI($product)
    {
        return [
            "Type" => "products",
            "id" => $product->getId(),
            "Attributes" => [
                "Brand" => $product->getBrand(),
                "Name" => $product->getName(),
                "Price" => $product->getPrice()
            ]
        ];
    }
}