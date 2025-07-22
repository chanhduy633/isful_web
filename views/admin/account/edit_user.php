<?php
$host = 'localhost';
$db   = 'websiteblog'; // Thay bằng tên cơ sở dữ liệu của bạn
$user = 'root'; // Thay bằng tên người dùng
$pass = ''; // Thay bằng mật khẩu
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}

if (!isset($_SESSION['userid']) || $_SESSION['username'] !== 'admin') {
    header('Location: /views/admin.php?page=dashboard');
    exit();
}

// Get user ID from query string
$userId = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $role = $_POST['role'];

    // Ensure only admin can change to admin role
    if ($_SESSION['role'] !== 'admin' && $role === 'admin') {
        $role = $_SESSION['role']; // Revert to current role if not admin
    }

    // Update the user information
    $updateStmt = $pdo->prepare("UPDATE users SET username = ?, role = ? WHERE id = ?");
    $updateStmt->execute([$username, $role, $userId]);
    // Điều hướng về trang quản lý tài khoản
    echo '<script>window.location.href = "/views/admin.php?page=manage_users";</script>';
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Chỉnh sửa tài khoản</title>
    <link rel="stylesheet" href="../../../public/css/bootstrap.min.css">
</head>

<body>
    <div class="container mt-5">
        <h2>Chỉnh sửa tài khoản</h2>
        <form method="post" action="">
            <div class="mb-3">
                <label for="username" class="form-label">Tên đăng nhập</label>
                <input type="text" class="form-control" id="username" name="username"
                    value="<?= htmlspecialchars($user['username']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="role" class="form-label">Vai trò</label>
                <select id="role" name="role" class="form-control">
                    <option value="user" <?= $user['role'] === 'user' ? 'selected' : ''; ?>>User</option>
                    <!-- <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option> -->
                    
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Cập nhật</button>
            <a href="/views/admin.php?page=manage_users" class="btn btn-secondary">Quay lại</a>
        </form>
    </div>
    <script src="../../../public/js/bootstrap.bundle.min.js"></script>
</body>

</html>