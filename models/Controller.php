<?php

class Controller
{
    static public function GetId($path)
    {
        $uri = explode("/", $path);
        return $uri[2];
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