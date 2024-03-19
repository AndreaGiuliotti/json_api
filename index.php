<?php
require_once "connection/Database.php";
require_once "models/Product.php";
require_once "models/Controller.php";

header("Content-type: application/vnd.api+json");

// Definisci un array associativo per mappare le route
$routes = [
    'GET' => [],
    'POST' => [],
    'PATCH' => [],
    'DELETE' => []
];

// Funzione per aggiungere una route
function addRoute($method, $path, $callback): void
{
    global $routes;
    $routes[$method][$path] = $callback;
}

// Funzione per ottenere il metodo della richiesta HTTP
function getRequestMethod(): string
{
    return $_SERVER['REQUEST_METHOD'];
}

// Funzione per ottenere il percorso richiesto
function getRequestPath()
{
    $path = $_SERVER['REQUEST_URI'];
    $path = parse_url($path, PHP_URL_PATH);
    return rtrim($path, '/');
}

// Funzione per gestire la richiesta
function handleRequest()
{
    global $routes;
    $method = getRequestMethod();
    $path = getRequestPath();

    // Verifica se esiste una route per il metodo e il percorso richiesti
    if (isset($routes[$method])) {
        foreach ($routes[$method] as $routePath => $callback) {
            // Verifica se il percorso richiesto corrisponde al percorso della route
            if (preg_match('#^' . $routePath . '$#', $path, $matches)) {
                // Chiamata al callback passando l'ID come parametro
                call_user_func_array($callback, $matches);
                return;
            }
        }
    }
    http_response_code(404);
    exit;
}

//adding route GET single product
addRoute('GET', '/products/(\d+)', function ($path) {
    $id = Controller::GetId($path);
    if (!$product = Product::Find_by_id($id)) { //controllo sulla validità dell'id
        http_response_code(404);
        exit;
    }
    $data = ["data" => Controller::GetJsonAPI($product)];
    http_response_code(200); //ok
    echo json_encode($data, JSON_PRETTY_PRINT);
});

//adding route GET all products
addRoute('GET', '/products', function () {
    if (!$products = Product::FetchAll()) {
        http_response_code(500); //server error
        exit;
    }
    $raw_json = [];
    foreach ($products as $product) {
        $raw_json[] = Controller::GetJsonAPI($product);
    }
    $data = ["data" => $raw_json];
    http_response_code(200); //ok
    echo json_encode($data, JSON_PRETTY_PRINT);
});

//adding route POST
addRoute('POST', '/products', function () {
    if (isset($_POST['data'])) {
        $data_raw = $_POST;
    } else {
        $data_raw = json_decode(file_get_contents("php://input"), true);
    }
    try {
        $attributes = $data_raw['data']['attributes'];
        if (!$product = Product::Create($attributes)) {
            http_response_code(500); //server error
            exit;
        }
        header("Location: /products/" . $product->getId());
        http_response_code(201);
        $json = Controller::GetJsonAPI($product);
        echo json_encode(["data" => $json], JSON_PRETTY_PRINT);
    } catch (Exception $e) {
        http_response_code(400); //bad request
    }
});

//adding route PATCH
addRoute('PATCH', '/products/(\d+)', function ($path) {
    $id = Controller::GetId($path);
    if (!$product = Product::Find_by_id($id)) { //controllo sulla validità dell'id
        http_response_code(404);
        exit;
    }
    $data_raw = json_decode(file_get_contents("php://input"), true);
    try {
        $attributes = $data_raw['data']['attributes'];
        if (!$new = $product->edit($attributes)) {
            http_response_code(404); //not found - edit ritorna un Find o false se non riesce a modificare il record
            exit;
        }
        header("Location: /products/" . $product->getId());
        http_response_code(200);
        $json = Controller::GetJsonAPI($new);
        echo json_encode(["data"=>$json]);
    } catch (Exception $e) {
        http_response_code(400); //bad request
    }
});

//adding route DELETE
addRoute('DELETE', '/products/(\d+)', function ($path) {
    $id = Controller::GetId($path);
    if (!$product = Product::Find_by_id($id)) { //controllo sulla validità dell'id
        http_response_code(404);
        exit;
    }
    if (!$product->delete()) {
        http_response_code(500); //server error
        exit;
    }
    http_response_code(204); //no content
});

try {
    handleRequest();
} catch (Exception $e) {
    echo json_encode(["Error" => $e], JSON_PRETTY_PRINT);
}
exit;