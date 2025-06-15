<?php
    require_once "credentials.php";
    header('Content-Type: application/json');

    //tokens
    if (!defined('API_TOKEN')) {
        http_response_code(500);
        echo json_encode(["error" => "api_token_not_defined"]);
        exit;
    }
    if (!isset($_GET['token']) || $_GET['token'] !== API_TOKEN) {
        http_response_code(403);
        echo json_encode(["error" => "invalid_api_token"]);
        exit;
    }

    //update
    if (isset($_GET['action']) && $_GET['action'] === 'update') {
        define('API_UPDATE', true);
        require_once "cron.php";

        http_response_code(200);
        echo json_encode(["status" => "update_completed"]);
        exit;
    }

    //data
    if (isset($_GET['action']) &&  $_GET['action'] === 'get') {
        $statusFile = 'status.json';
        if (!file_exists($statusFile)) {
            http_response_code(500);
            echo json_encode(["error" => "status_file_not_found"]);
            exit;
        }

        $status = file_get_contents($statusFile);
        if ($status === false) {
            http_response_code(500);
            echo json_encode(["error" => "failed_to_read_status_file"]);
            exit;
        }

        http_response_code(200);
        echo $status;
        exit;
    }

    http_response_code(400);
    echo json_encode(["error" => "invalid_action"]);
?>