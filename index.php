<?php
require_once "connection/Database.php";
require_once "Product.php";
require_once "Controller.php";

header("Content-type: application/json; charset=UTF-8");

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
    echo json_encode(["Error" => "Route Method Not Set"], JSON_PRETTY_PRINT);
    exit;
}

//adding route GET single product
addRoute('GET', '/products/(\d+)', function ($path) {
    $id = Controller::CheckPath($path);
    if ($id == 404 || !$id) { //controllo sulla validità del path
        http_response_code(404);
        echo json_encode(["Error" => "ID not acceptable"], JSON_PRETTY_PRINT);
        exit;
    }
    if (!$product = Product::Find_by_id($id)) {
        http_response_code(404); //not found
        echo json_encode(["Error" => "ID Not Acceptable"], JSON_PRETTY_PRINT);
        exit;
    }
    $data = ["Data" => Controller::GetJsonAPI($product)];
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
    $data = ["Data" => $raw_json];
    http_response_code(200); //ok
    echo json_encode($data, JSON_PRETTY_PRINT);
});

//adding route POST
addRoute('POST', '/products', function () {
    $data = (array)json_decode(file_get_contents("php://input"));
    if (!$product = Product::Create($data)) {
        http_response_code(500); //server error
        json_encode(["Error" => "Params Not Acceptable"], JSON_PRETTY_PRINT);
        exit;
    }
    http_response_code(201);
    header("Location: /products/" . $product->getId());
    $json = Controller::GetJsonAPI($product);
    echo json_encode(["Data" => $json], JSON_PRETTY_PRINT);
});

//adding route PATCH
addRoute('PATCH', '/products/(\d+)', function ($path) {
    $id = Controller::CheckPath($path);
    if ($id == 404 || !$id) { //controllo sulla validità del path
        http_response_code(404);
        echo json_encode(["Error" => "ID not acceptable"], JSON_PRETTY_PRINT);
        exit;
    }
    if (!$product = Product::Find_by_id($id)) {
        http_response_code(404); //not found
        echo json_encode(["Error" => "ID Not Acceptable"], JSON_PRETTY_PRINT);
        exit;
    }
    $data = (array)json_decode(file_get_contents("php://input"));
    if (!$new = $product->edit($data)) {
        http_response_code(404); //not found - edit ritorna un Find o false se non riesce a modificare il record
        json_encode(["Error" => "Params Not Acceptable"], JSON_PRETTY_PRINT);
        exit;
    }
    http_response_code(200);
    header("Location: /products/" . $product->getId());
    $json = Controller::GetJsonAPI($new);
    echo json_encode(["Data" => $json], JSON_PRETTY_PRINT);
});

//adding route DELETE
addRoute('DELETE', '/products/(\d+)', function ($path) {
    $id = Controller::CheckPath($path);
    if ($id == 404 || !$id) { //controllo sulla validità del path
        http_response_code(404);
        echo json_encode(["Error" => "ID Not Acceptable"], JSON_PRETTY_PRINT);
        exit;
    }
    if (!$product = Product::Find_by_id($id)) {
        http_response_code(404); //not found
        echo json_encode(["Error" => "ID Not Acceptable"], JSON_PRETTY_PRINT);
        exit;
    }
    if (!$product->delete()) {
        http_response_code(500); //server error
        exit;
    }
    http_response_code(204);
    exit;
});

try {
    handleRequest();
} catch (Exception $e) {
    echo json_encode(["Error" => $e], JSON_PRETTY_PRINT);
}
exit;