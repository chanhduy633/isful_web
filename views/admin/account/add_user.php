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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $role = $_POST['role'];

    // Insert the new user
    $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
    $stmt->execute([$username, $password, $role]);
    echo '<script>window.location.href = "/views/admin.php?page=manage_users";</script>';
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Thêm tài khoản mới</title>
    <link rel="stylesheet" href="../../../public/css/bootstrap.min.css">
</head>

<body>
    <div class="container mt-5">
        <h2>Thêm tài khoản mới</h2>
        <form method="post" action="">
            <div class="mb-3">
                <label for="username" class="form-label">Tên đăng nhập</label>
                <input type="text" class="form-control" id="username" name="username" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Mật khẩu</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <div class="mb-3">
                <label for="role" class="form-label">Vai trò</label>
                <select id="role" name="role" class="form-control">
                    <option value="user">User</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Thêm</button>
            <a href="/views/admin.php?page=manage_users" class="btn btn-secondary">Quay lại</a>
        </form>
    </div>
    <script src="../../../public/js/bootstrap.bundle.min.js"></script>
</body>

</html>