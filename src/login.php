<?php
//cap. 60. el login no es restful, se puede convertir pasandole las cosas en el front (ver video)
declare(strict_types=1);

use App\JWTCodec;
use App\RefreshTokenGateway;
use App\UserGateway;

require __DIR__ . "/bootstrap.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405); //method not allowed
    header("Allow: POST");
    exit;
}

$data = (array) json_decode(file_get_contents("php://input"), true); //datos enviados en la request

if (!array_key_exists("username", $data) ||
    !array_key_exists("password", $data)) {

        http_response_code(400);
        echo json_encode(["message" => "Please, introduce login credentials"]);
        exit;
}

//authenticate
$userGateway = new UserGateway();

$user = $userGateway->getByUsername($data["username"]);

if ($user === false) {
    http_response_code(401);
    echo json_encode(["message" => "Invalid authentication"]);
    exit;
}

if (!password_verify($data["password"], $user["password_hash"])) {
    http_response_code(401);
    echo json_encode(["message" => "Invalid authentication"]);
    exit;
}

$codec = new JWTCodec();

require __DIR__ . "/tokens.php";


$refreshTokenGateway = new RefreshTokenGateway();
$refreshTokenGateway->create($refreshToken, $refreshTokenExpiry);