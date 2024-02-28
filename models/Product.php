<?php

class Product
{
    private $id, $nome, $marca, $prezzo;

    function getId()
    {
        return $this->id;
    }

    function getBrand()
    {
        return $this->marca;
    }

    function getName()
    {
        return $this->nome;
    }

    function getPrice()
    {
        return $this->prezzo;
    }

    public static function Create($params)
    {
        /*$duplicate = self::CheckDuplicates($params);
        if ($duplicate) {
            return false;
        }*/
        $pdo = self::connectToDatabase();
        $stmt = $pdo->prepare("insert into products (marca, nome, prezzo) values (:marca, :nome, :prezzo)");
        $stmt->bindParam(":marca", $params['marca']);
        $stmt->bindParam(":nome", $params['nome']);
        $stmt->bindParam(":prezzo", $params['prezzo']);
        if (!$stmt->execute()) {
            return false;
        }
        return self::getLastInsert();
    }

    private static function CheckDuplicates($params)
    {
        return self::Find($params);
    }

    public static function getLastInsert()
    {
        $pdo = self::connectToDatabase();
        return $pdo->query("select * from products order by id desc limit 1")->fetchObject("Product");
    }

    public static function Find($params)
    {
        $pdo = self::connectToDatabase();
        $stmt = $pdo->prepare("select * from products where nome = :nome and marca = :marca and prezzo = :prezzo");
        $stmt->bindParam(":nome", $params['nome']);
        $stmt->bindParam(":marca", $params['marca']);
        $stmt->bindParam(":prezzo", $params['prezzo']);
        if (!$stmt->execute()) {
            return false;
        }
        return $stmt->fetchObject('Product');
    }

    public static function Find_by_id($id)
    {
        $pdo = self::connectToDatabase();
        $stmt = $pdo->prepare("select * from products where id= :id");
        $stmt->bindParam(":id", $id);
        if (!$stmt->execute()) {
            return false;
        }
        return $stmt->fetchObject('Product');
    }

    public static function FetchAll()
    {
        $pdo = self::connectToDatabase();
        $sql = "select * from products";
        return $pdo->query($sql)->fetchAll(PDO::FETCH_CLASS,"Product");
    }

    public function edit($params)
    {
        $pdo = self::connectToDatabase();
        if(!isset($params['marca'])){
            $params['marca'] = $this->getBrand();
        }
        if(!isset($params['nome'])){
            $params['nome'] = $this->getName();
        }
        if(!isset($params['prezzo'])){
            $params['prezzo'] = $this->getPrice();
        }
        $id = $this->getId();
        $stmt = $pdo->prepare("update products set marca=:marca,nome=:nome,prezzo=:prezzo where id=:id");
        $stmt->bindParam(":marca", $params['marca']);
        $stmt->bindParam(":nome", $params['nome']);
        $stmt->bindParam(":prezzo", $params['prezzo']);
        $stmt->bindParam(":id", $id);
        if (!$stmt->execute()) {
            return false;
        }
        return Product::Find_by_id($id);
    }

    public function delete()
    {
        $id = $this->getId();
        $pdo = self::connectToDatabase();
        $stmt = $pdo->prepare("delete from products where id=:id");
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }

    private static function connectToDatabase()
    {
        $database = new Database("192.168.2.200", "andrea_giuliotti", "savagery.seconding.tossing.");
        return $database->connect("andrea_giuliotti_ecommerce");
    }
}