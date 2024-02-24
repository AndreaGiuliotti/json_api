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
            "type" => "products",
            "id" => $product->getId(),
            "attributes" => [
                "nome" => $product->getName(),
                "marca" => $product->getBrand(),
                "prezzo" => $product->getPrice()
            ]
        ];
    }
}