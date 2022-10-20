<?php

declare(strict_types=1);

use App\Authentication;
use App\JWTCodec;
use App\Task\Controller\TaskController;
use App\Task\Infrastructure\TaskRepository;
use App\UserGateway;

require dirname(__DIR__) . "/src/bootstrap.php";


// Routing
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$parts = explode("/", $path);
 
$resource = $parts[3] ?? null;
$id = $parts[4] ?? null;
$method = $_SERVER['REQUEST_METHOD'];

if ($resource != "tasks") {
    http_response_code(404);
    exit;
}

// User authentication 
$userGateway = new UserGateway();
$codec = new JWTCodec();
$auth = new Authentication($userGateway, $codec);

// if (!$auth->authenticateAccessToken()) {
//     exit;
// }

// if (!$auth->authenticateAPIKey()) {
//     exit;
// }

if (!$auth->authenticateJwtToken()) {
    exit;
}

// Process request
$userId = $auth->getUserId();

$controller = new TaskController($userId);
$controller->processRequest($method, $id);
