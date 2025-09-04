<?php
header('Content-Type: application/json');
require_once '../connect/db.php';

$response = ['success' => false, 'job' => null, 'message' => ''];

try {
    $id = $_GET['id'] ?? 0;

    if (!$id || !is_numeric($id)) {
        $response['message'] = 'ID công việc không hợp lệ';
        echo json_encode($response);
        exit();
    }

    // Lấy thông tin công việc + thông tin người đăng (cả users và google_users)
    $stmt = $pdo->prepare("
        SELECT r.*,
    CASE 
        WHEN u.id IS NOT NULL THEN u.username
        WHEN g.id IS NOT NULL THEN g.name
        ELSE 'Người dùng không xác định'
    END AS display_name,
    COALESCE(u.username, g.name) AS username,
    CASE 
        WHEN u.id IS NOT NULL THEN 'normal'
        WHEN g.id IS NOT NULL THEN 'google'
        ELSE 'unknown'
    END AS login_type
FROM recruitments r
LEFT JOIN users u ON r.normal_user_id = u.id
LEFT JOIN google_users g ON r.google_user_id = g.id
        WHERE r.id = ?
        LIMIT 1
    ");

    $stmt->execute([$id]);
    $job = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$job) {
        $response['message'] = 'Không tìm thấy công việc này';
        echo json_encode($response);
        exit();
    }

    // Làm sạch dữ liệu trước khi trả về
    $cleanJob = [
        'id' => (int)$job['id'],
        'job_title' => htmlspecialchars($job['job_title']),
        'company_name' => htmlspecialchars($job['company_name']),
        'job_description' => nl2br(htmlspecialchars($job['job_description'])), // Giữ xuống dòng
        'salary' => $job['salary'] ? htmlspecialchars($job['salary']) : null,
        'contact_phone' => htmlspecialchars($job['contact_phone']),
        'contact_email' => htmlspecialchars($job['contact_email']),
        'company_address' => $job['company_address'] ? nl2br(htmlspecialchars($job['company_address'])) : null,
        'created_at' => $job['created_at'],
        'username' => htmlspecialchars($job['display_name']),
        'user_type' => $job['user_type']
    ];

    $response['success'] = true;
    $response['job'] = $cleanJob;
} catch (Exception $e) {
    $response['message'] = 'Có lỗi xảy ra: ' . $e->getMessage();
}

echo json_encode($response);
