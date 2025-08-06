<?php
// google_config.php
// File cấu hình cho Google OAuth

// HƯỚNG DẪN:
// 1. Thay YOUR_ACTUAL_CLIENT_ID bằng Client ID thực tế từ Google Cloud Console
// 2. Client ID có dạng: 123456789-xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx.apps.googleusercontent.com

define('GOOGLE_CLIENT_ID', '160728313847-ljfjpv8002o3dijhapvirf7q7dd2bh5t.apps.googleusercontent.com');

// Nếu cần sử dụng Client Secret (cho server-side verification)
define('GOOGLE_CLIENT_SECRET', 'YOUR_CLIENT_SECRET');

// Domain được phép (cho production)
define('ALLOWED_DOMAINS', [
    'localhost',
    '127.0.0.1',
    'yourdomain.com' // Thay bằng domain thực tế
]);

// Kiểm tra môi trường
function isLocalhost() {
    $whitelist = array(
        '127.0.0.1',
        '::1',
        'localhost'
    );
    
    return in_array($_SERVER['REMOTE_ADDR'], $whitelist) || 
           in_array($_SERVER['HTTP_HOST'], $whitelist) ||
           strpos($_SERVER['HTTP_HOST'], 'localhost') !== false;
}

// Lấy base URL hiện tại
function getBaseURL() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    return $protocol . '://' . $host;
}
?>