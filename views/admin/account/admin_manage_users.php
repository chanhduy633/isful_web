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

// Check if the user is logged in and is an admin
if (!isset($_SESSION['userid']) || $_SESSION['username'] !== 'admin') {
    header('Location: /views/admin.php?page=dashboard');
    exit();
}

// Fetch all users from the database
$sql = "SELECT * FROM users";
$stmt = $pdo->query($sql);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle deletion of a user
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_user'])) {
    $userId = $_POST['user_id'];
    $deleteStmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $deleteStmt->execute([$userId]);
    // Điều hướng về trang quản lý tài khoản
    header('Location: /views/admin.php?page=manage_users');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý tài khoản</title>
    <link rel="stylesheet" href="../../../public/css/bootstrap.min.css">
</head>

<body>
    <div class="container">
        <div class="d-flex justify-content-between align-items-center pt-3 pb-2 mb-3 border-bottom">
            <h1 class=" h2 mt-4">Quản lý tài khoản</h1>
        </div>

        <table class="table table-bordered mt-4">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tên đăng nhập</th>
                    <th>Vai trò</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= $user['id']; ?></td>
                        <td><?= htmlspecialchars($user['username']); ?></td>
                        <td><?= $user['role']; ?></td>
                        <td>
                            <!-- Điều chỉnh liên kết nút "Sửa" -->
                            <a href="/views/admin.php?page=edit_user&id=<?= $user['id']; ?>" class="btn btn-warning btn-sm">Sửa</a>
                            <form method="post" action="" class="d-inline">
                                <input type="hidden" name="user_id" value="<?= $user['id']; ?>">
                                <button type="submit" name="delete_user" class="btn btn-danger btn-sm"
                                    onclick="return confirm('Bạn có chắc chắn muốn xóa tài khoản này không?');">
                                    Xóa
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <!-- Sửa liên kết của nút "Thêm tài khoản mới" -->
        <a href="/views/admin.php?page=add_user" class="btn btn-primary">Thêm tài khoản mới</a>
    </div>
    <script src="../../../public/js/bootstrap.bundle.min.js"></script>
</body>

</html>