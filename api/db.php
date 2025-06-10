<?php
$host = "localhost";
$user = "taskuser";
$password = "khtn2022"; // Đổi theo config của bạn
$dbname = "taskmanager"; // Đổi tên CSDL theo dự án của bạn

$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["message" => "Database connection failed"]);
    exit;
}
?>
