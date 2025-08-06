<?php
session_start();
require '../connect/db.php';
require 'google_config.php'; // Include Google config

// Cấu hình Google OAuth
$google_client_id = GOOGLE_CLIENT_ID;

// Xử lý đăng nhập thông thường
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] === 'login') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE username = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['userid'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['login_type'] = 'normal';
        $_SESSION['is_system_admin'] = $user['is_system_admin'] ?? 0;

        echo json_encode(['success' => true, 'redirect' => ($_SESSION['role'] == 'admin') ? '/views/admin.php?page=category' : '/views/index.php']);
        exit();
    } else {
        echo json_encode(['success' => false, 'error' => 'Tên đăng nhập hoặc mật khẩu không đúng!']);
        exit();
    }
}

// Xử lý đăng ký
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] === 'register') {
    $username = trim($_POST['reg_username']);
    $password = $_POST['reg_password'];
    $confirm_password = $_POST['reg_confirm_password'];

    if (empty($username) || empty($password)) {
        echo json_encode(['success' => false, 'error' => 'Vui lòng điền đầy đủ thông tin!']);
        exit();
    }

    if ($password !== $confirm_password) {
        echo json_encode(['success' => false, 'error' => 'Mật khẩu xác nhận không khớp!']);
        exit();
    }

    if (strlen($password) < 6) {
        echo json_encode(['success' => false, 'error' => 'Mật khẩu phải có ít nhất 6 ký tự!']);
        exit();
    }

    // Kiểm tra username đã tồn tại
    $check_stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
    $check_stmt->execute([$username]);

    if ($check_stmt->fetchColumn() > 0) {
        echo json_encode(['success' => false, 'error' => 'Tên đăng nhập đã tồn tại!']);
        exit();
    }

    // Tạo tài khoản mới
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);
    $stmt = $pdo->prepare("INSERT INTO users (username, password, role, is_system_admin) VALUES (?, ?, 'user', 0)");

    if ($stmt->execute([$username, $hashed_password])) {
        echo json_encode(['success' => true, 'message' => 'Đăng ký thành công! Vui lòng đăng nhập.']);
        exit();
    } else {
        echo json_encode(['success' => false, 'error' => 'Có lỗi xảy ra khi tạo tài khoản!']);
        exit();
    }
}

// Xử lý đăng nhập Google
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] === 'google_login') {
    $id_token = $_POST['google_token'];

    // Verify token với Google
    $verify_url = "https://oauth2.googleapis.com/tokeninfo?id_token=" . $id_token;
    $response = file_get_contents($verify_url);
    $google_user_data = json_decode($response, true);

    if (isset($google_user_data['aud']) && $google_user_data['aud'] === $google_client_id) {
        $google_id = $google_user_data['sub'];
        $email = $google_user_data['email'];
        $name = $google_user_data['name'];
        $picture = $google_user_data['picture'] ?? '';

        // Kiểm tra xem người dùng đã tồn tại chưa
        $sql = "SELECT * FROM google_users WHERE google_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$google_id]);
        $google_user = $stmt->fetch();

        if (!$google_user) {
            // Tạo tài khoản mới cho người dùng Google
            $insert_sql = "INSERT INTO google_users (google_id, email, name, picture, role) VALUES (?, ?, ?, ?, 'user')";
            $insert_stmt = $pdo->prepare($insert_sql);
            $insert_stmt->execute([$google_id, $email, $name, $picture]);

            $google_user = [
                'id' => $pdo->lastInsertId(),
                'google_id' => $google_id,
                'email' => $email,
                'name' => $name,
                'picture' => $picture,
                'role' => 'user'
            ];
        }

        $_SESSION['userid'] = $google_user['id'];
        $_SESSION['username'] = $google_user['name'];
        $_SESSION['email'] = $google_user['email'];
        $_SESSION['role'] = $google_user['role'];
        $_SESSION['login_type'] = 'google';
        $_SESSION['picture'] = $google_user['picture'];

        echo json_encode(['success' => true, 'redirect' => ($_SESSION['role'] == 'admin') ? '/views/admin.php?page=category' : '/views/index.php']);
        exit();
    } else {
        echo json_encode(['success' => false, 'error' => 'Token Google không hợp lệ!']);
        exit();
    }
}

// Xử lý logout
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_destroy();
    header('Location: /views/index.php');
    exit();
}
?>