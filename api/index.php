<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// Cho phép các phương thức và headers phù hợp cho CORS
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Kết nối database
require_once "db.php";

// Lấy method và đường dẫn
$method = $_SERVER['REQUEST_METHOD'];
$uri = explode('/', trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/'));
$endpoint = isset($uri[1]) ? $uri[1] : null;
$id = isset($uri[2]) ? intval($uri[2]) : null;

// Đọc dữ liệu JSON từ request body
$input = json_decode(file_get_contents("php://input"), true);

if ($endpoint !== 'api') {
    http_response_code(404);
    echo json_encode(["message" => "Endpoint not found"]);
    exit;
}

switch ($method) {
    case 'GET':
        // Lấy danh sách tasks
        $sql = "SELECT * FROM Tasks";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        $tasks = $result->fetch_all(MYSQLI_ASSOC);
        echo json_encode($tasks);
        break;

    case 'POST':
        // Thêm task mới
        $stmt = $conn->prepare("INSERT INTO Tasks (Name, Description, DueDate, Status) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $input['Name'], $input['Description'], $input['DueDate'], $input['Status']);
        $stmt->execute();
        $id = $stmt->insert_id;
        echo json_encode([
            "ID" => $id,
            "Name" => $input['Name'],
            "Description" => $input['Description'],
            "DueDate" => $input['DueDate'],
            "Status" => $input['Status']
        ]);
        break;

    case 'PUT':
        // Cập nhật task
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
        // Xoá task
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
        // Đáp ứng preflight CORS
        http_response_code(200);
        break;

    default:
        http_response_code(405);
        echo json_encode(["message" => "Method not allowed"]);
        break;
}
?>
