<?php

namespace auth;

use db\STOCKFILE_CONFIG;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../utils/db.php';

use \Firebase\JWT\JWT;

function checkAuthorization()
{
    $allHeaders = getallheaders();
    $authHeader = isset($allHeaders['Authorization']) ? trim($allHeaders['Authorization']) : null;
    if (!$authHeader) {
        echo json_encode([
            "status" => 0,
            "status_message" => "No Authorization Header given!"
        ]);
        return;
    }

    // Parse out token
    $jwt = trim(str_replace('Bearer', '', $authHeader));

    try {
        $secretKey = STOCKFILE_CONFIG::$STOCKFILE_SECRET_AUTH_KEY;
        $token = JWT::decode($jwt, $secretKey, array('HS512'));
        $token_array = json_decode(json_encode($token), true);
        echo json_encode([
            "status" => 1,
            "status_message" => "Successful Authorized Request!",
        ]);
        return $token_array;
    } catch (Exception $e) {
        header('HTTP/1.0 401 Unauthorized');
        echo json_encode([
            "status" => 0,
            "status_message" => "Unsuccessful Attempt At Authorized Request!",
        ]);
        return false;
    }
}
