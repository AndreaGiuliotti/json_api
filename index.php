<?php
require_once "connection/Database.php";
require_once "Product.php";

header("Content-type: application/json; charset=UTF-8");
$uri = explode("/", $_SERVER["REQUEST_URI"]);

if ($uri[1] != "products") {
    http_response_code(404);
    exit;
}

$id = $parts[2] ?? null;


// Definisci un array associativo per mappare le route
$routes = [
    'GET' => [],
    'POST' => [],
    'PATCH' => [],
    'DELETE' => []
];

// Funzione per aggiungere una route
function addRoute($method, $path, $callback) {
    global $routes;
    $routes[$method][$path] = $callback;
}

// Funzione per ottenere il metodo della richiesta HTTP
function getRequestMethod() {
    return $_SERVER['REQUEST_METHOD'];
}

// Funzione per ottenere il percorso richiesto
function getRequestPath() {
    $path = $_SERVER['REQUEST_URI'];
    $path = parse_url($path, PHP_URL_PATH);
    return rtrim($path, '/');
}

// Funzione per gestire la richiesta
function handleRequest() {
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

    // Ritorna un errore 404 se la route non è stata trovata
    http_response_code(404);
    echo "404 Not Found";
}

// Aggiungi le tue route qui
addRoute('GET', '/products/(\d+)', function($id) {
    $product = Product::Find_by_id($id);
});
addRoute('GET', '/products', function() {
    $products = Product::FetchAll();
});
addRoute('POST', '/products', function (){
    $data = file_get_contents("php://input");
    $product = Product::Create($data);
});
addRoute('PATCH', '/products', function ($id){
    $product = Product::Find_by_id($id);
    $data = file_get_contents("php://input");
    $product->edit($data);
});
addRoute('DELETE', '/products/(\d+)', function($id) {
    $product = Product::Find_by_id($id);
    $product->delete($id);
});

handleRequest();