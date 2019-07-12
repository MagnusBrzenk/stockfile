<?php

namespace files;

use file_handlers;

require_once __DIR__ . '/../utils/fileHandlers.php';

function handleHttpRequest()
{

    $request_method = $_SERVER["REQUEST_METHOD"];
    switch ($request_method) {
        case 'GET':
            // Parse http request
            $file_name = !!empty($_GET["file_name"]) ? null : $_GET["file_name"];
            // Handle extracted params
            $response = file_handlers\get_files($file_name);
            header('Content-Type: application/json');
            echo json_encode($response);
            break;
        case 'POST':
            // Parse http request
            $post_body = json_decode(file_get_contents("php://input"), true);
            if (!is_array($post_body)) $post_body = array($post_body);

            // echo "\n\n";
            // print_r($post_body);
            // echo "\n\n";
            // return;

            // Handle extracted params
            $response = file_handlers\insert_files($post_body);


            // echo "\n\n";
            // print_r($response);
            // echo "\n\n";


            // return;

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
