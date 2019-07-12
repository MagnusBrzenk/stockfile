<?php

namespace files;

use file_handlers;
use auth;

require_once __DIR__ . '/../utils/fileHandlers.php';
require_once __DIR__ . '/auth.php';

function handleHttpRequest()
{
    // Check if request is wt-authorized
    $decodedToken = auth\checkAuthorization();
    if (!$decodedToken) return;

    $request_method = $_SERVER["REQUEST_METHOD"];
    switch ($request_method) {
        case 'GET':

            print_r([$decodedToken]);


            // Parse http request
            $file_name = !!empty($_GET["file_name"]) ? null : $_GET["file_name"];
            $user_name = $decodedToken['data']['username'];

            print_r([$user_name]);

            // Handle extracted params
            $response = file_handlers\get_files($file_name, $user_name);
            header('Content-Type: application/json');
            echo json_encode($response);
            break;
        case 'POST':
            // Parse http request
            $post_body = json_decode(file_get_contents("php://input"), true);
            if (!is_array($post_body)) $post_body = array($post_body);
            // Handle extracted params
            $response = file_handlers\insert_files($post_body);
            header('Content-Type: application/json');
            echo json_encode($response);
            break;
        case 'PUT':
            // Parse http request
            $file_name = !!empty($_GET["file_name"]) ? null : $_GET["file_name"];
            $put_body = json_decode(file_get_contents("php://input"), true);
            // Handle extracted params
            $response = file_handlers\update_file($file_name, $put_body);
            header('Content-Type: application/json');
            echo json_encode($response);
            break;
        case 'DELETE':
            // Parse http request
            $file_name = !!empty($_GET["file_name"]) ? null : $_GET["file_name"];
            echo file_handlers\delete_file($file_name);
            // Handle extracted params
            $response = file_handlers\delete_file($file_name);
            header('Content-Type: application/json');
            echo json_encode($response);
            break;
        default:
            // Invalid Request Method
            header("HTTP/1.0 405 Method Not Allowed");
            break;
    }
}
handleHttpRequest();
