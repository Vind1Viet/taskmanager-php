<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Kết nối database
require_once "db.php";

// Lấy method và URI
$method = $_SERVER['REQUEST_METHOD'];
$uri = explode('/', trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/'));

// Chỉ xử lý nếu URI bắt đầu bằng 'api'
if (!isset($uri[0]) || $uri[0] !== 'api') {
    http_response_code(404);
    echo json_encode(["message" => "Endpoint not found"]);
    exit;
}

// Nếu có ID thì nằm ở uri[1]
$id = isset($uri[1]) ? intval($uri[1]) : null;

// Đọc JSON body nếu cần
$input = json_decode(file_get_contents("php://input"), true);

switch ($method) {
    case 'GET':
        if ($id) {
            $stmt = $conn->prepare("SELECT * FROM Tasks WHERE ID = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $task = $result->fetch_assoc();

            if ($task) {
                echo json_encode($task);
            } else {
                http_response_code(404);
                echo json_encode(["message" => "Task not found"]);
            }
        } else {
            $result = $conn->query("SELECT * FROM Tasks");
            $tasks = $result->fetch_all(MYSQLI_ASSOC);
            echo json_encode($tasks);
        }
        break;

    case 'POST':
        $stmt = $conn->prepare("INSERT INTO Tasks (Name, Description, DueDate, Status) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $input['Name'], $input['Description'], $input['DueDate'], $input['Status']);
        $stmt->execute();
        $newId = $stmt->insert_id;

        echo json_encode([
            "ID" => $newId,
            "Name" => $input['Name'],
            "Description" => $input['Description'],
            "DueDate" => $input['DueDate'],
            "Status" => $input['Status']
        ]);
        break;

    case 'PUT':
        if (!$id) {
            http_response_code(400);
            echo json_encode(["message" => "Task ID is required"]);
            exit;
        }

        $stmt = $conn->prepare("UPDATE Tasks SET Name=?, Description=?, DueDate=?, Status=? WHERE ID=?");
        $stmt->bind_param("ssssi", $input['Name'], $input['Description'], $input['DueDate'], $input['Status'], $id);
        $stmt->execute();
        echo json_encode(["message" => "Task updated"]);
        break;

    case 'DELETE':
        if (!$id) {
            http_response_code(400);
            echo json_encode(["message" => "Task ID is required"]);
            exit;
        }

        $stmt = $conn->prepare("DELETE FROM Tasks WHERE ID=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        echo json_encode(["message" => "Task deleted"]);
        break;

    case 'OPTIONS':
        http_response_code(200);
        break;

    default:
        http_response_code(405);
        echo json_encode(["message" => "Method not allowed"]);
        break;
}
?>
