<?php
$host = 'localhost';
$db   = 'websiteblog';
$user = 'root';
$pass = '';
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
if (!isset($_SESSION['userid']) || $_SESSION['role'] !== 'admin') {
    header('Location: /views/admin.php?page=dashboard');
    exit();
}

// Fetch regular users from both tables (exclude system admin)
$users = [];

// Lấy người dùng thông thường từ bảng users (không phải system admin)
$sql_normal = "SELECT id, username, 'normal' as login_type, role, 'users' as table_source FROM users WHERE is_system_admin != 1 OR is_system_admin IS NULL";
$stmt_normal = $pdo->query($sql_normal);
$normal_users = $stmt_normal->fetchAll(PDO::FETCH_ASSOC);

// Lấy người dùng Google
$sql_google = "SELECT id, name as username, 'google' as login_type, role, 'google_users' as table_source, email FROM google_users";
$stmt_google = $pdo->query($sql_google);
$google_users = $stmt_google->fetchAll(PDO::FETCH_ASSOC);

// Kết hợp hai mảng
$users = array_merge($normal_users, $google_users);

// Handle deletion of a user
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_user'])) {
    $userId = $_POST['user_id'];
    $tableSource = $_POST['table_source'];
    
    if ($tableSource === 'users') {
        $deleteStmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND (is_system_admin != 1 OR is_system_admin IS NULL)");
    } else {
        $deleteStmt = $pdo->prepare("DELETE FROM google_users WHERE id = ?");
    }
    
    $deleteStmt->execute([$userId]);
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
            <h1 class="h2 mt-4">Quản lý tài khoản</h1>
        </div>

        <table class="table table-bordered mt-4">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tên đăng nhập</th>
                    <th>Loại tài khoản</th>
                    <th>Email</th>
                    <th>Vai trò</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= $user['id']; ?></td>
                        <td><?= htmlspecialchars($user['username']); ?></td>
                        <td>
                            <?php if ($user['login_type'] === 'google'): ?>
                                <span class="badge bg-info">Google</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Thông thường</span>
                            <?php endif; ?>
                        </td>
                        <td><?= isset($user['email']) ? htmlspecialchars($user['email']) : 'N/A'; ?></td>
                        <td>
                            <?php if ($user['role'] === 'admin'): ?>
                                <span class="badge bg-success">Admin</span>
                            <?php else: ?>
                                <span class="badge bg-primary">User</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($user['login_type'] === 'normal'): ?>
                                <a href="/views/admin.php?page=edit_user&id=<?= $user['id']; ?>&type=normal" class="btn btn-warning btn-sm">Sửa</a>
                            <?php else: ?>
                                <a href="/views/admin.php?page=edit_user&id=<?= $user['id']; ?>&type=google" class="btn btn-warning btn-sm">Sửa</a>
                            <?php endif; ?>
                            
                            <form method="post" action="" class="d-inline">
                                <input type="hidden" name="user_id" value="<?= $user['id']; ?>">
                                <input type="hidden" name="table_source" value="<?= $user['table_source']; ?>">
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
        
        <a href="/views/admin.php?page=add_user" class="btn btn-primary">Thêm tài khoản mới</a>
    </div>
    <script src="../../../public/js/bootstrap.bundle.min.js"></script>
</body>
</html>