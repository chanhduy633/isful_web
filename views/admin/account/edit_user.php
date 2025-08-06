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

if (!isset($_SESSION['userid']) || $_SESSION['role'] !== 'admin') {
    header('Location: /views/admin.php?page=dashboard');
    exit();
}

// Get user ID and type from query string
$userId = $_GET['id'];
$userType = $_GET['type'] ?? 'normal'; // normal or google

$success = false;
$error = '';
$user = null;

// Fetch user information based on type
if ($userType === 'google') {
    $stmt = $pdo->prepare("SELECT id, name as username, email, role, 'google' as login_type FROM google_users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
} else {
    $stmt = $pdo->prepare("SELECT id, username, role, 'normal' as login_type FROM users WHERE id = ? AND (is_system_admin != 1 OR is_system_admin IS NULL)");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
}

if (!$user) {
    header('Location: /views/admin.php?page=manage_users');
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $role = 'user'; // Force role to always be 'user'
    $new_password = trim($_POST['new_password']);

    // Validate input
    if (empty($username)) {
        $error = 'Tên đăng nhập không được để trống!';
    } else {
        // Check if username already exists (except current user)
        if ($userType === 'google') {
            $check_stmt = $pdo->prepare("SELECT COUNT(*) FROM google_users WHERE name = ? AND id != ?");
        } else {
            $check_stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ? AND id != ?");
        }
        $check_stmt->execute([$username, $userId]);
        
        if ($check_stmt->fetchColumn() > 0) {
            $error = 'Tên đăng nhập đã tồn tại!';
        } else {
            if ($userType === 'google') {
                // Update Google user (always as 'user' role)
                $updateStmt = $pdo->prepare("UPDATE google_users SET name = ?, role = ? WHERE id = ?");
                $updateStmt->execute([$username, $role, $userId]);
            } else {
                // Update normal user (always as 'user' role)
                if (!empty($new_password)) {
                    $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
                    $updateStmt = $pdo->prepare("UPDATE users SET username = ?, password = ?, role = ? WHERE id = ? AND (is_system_admin != 1 OR is_system_admin IS NULL)");
                    $updateStmt->execute([$username, $hashed_password, $role, $userId]);
                } else {
                    $updateStmt = $pdo->prepare("UPDATE users SET username = ?, role = ? WHERE id = ? AND (is_system_admin != 1 OR is_system_admin IS NULL)");
                    $updateStmt->execute([$username, $role, $userId]);
                }
            }
            $success = true;
        }
    }
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
        
        <?php if ($success): ?>
            <div class="alert alert-success">
                Tài khoản đã được cập nhật thành công!
                <script>
                    setTimeout(function() {
                        window.location.href = "/views/admin.php?page=manage_users";
                    }, 2000);
                </script>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <div class="card">
            <div class="card-header">
                <h5>Thông tin tài khoản</h5>
                <small class="text-muted">
                    Loại tài khoản: 
                    <?php if ($userType === 'google'): ?>
                        <span class="badge bg-info">Google</span>
                    <?php else: ?>
                        <span class="badge bg-secondary">Thông thường</span>
                    <?php endif; ?>
                </small>
            </div>
            <div class="card-body">
                <form method="post" action="">
                    <div class="mb-3">
                        <label for="username" class="form-label">Tên đăng nhập <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="username" name="username"
                            value="<?= htmlspecialchars($user['username']); ?>" required>
                    </div>
                    
                    <?php if ($userType === 'google'): ?>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?= htmlspecialchars($user['email']); ?>" readonly>
                            <div class="form-text">Email Google không thể thay đổi.</div>
                        </div>
                    <?php else: ?>
                        <div class="mb-3">
                            <label for="new_password" class="form-label">Mật khẩu mới (để trống nếu không muốn thay đổi)</label>
                            <input type="password" class="form-control" id="new_password" name="new_password">
                            <div class="form-text">Chỉ nhập mật khẩu mới nếu bạn muốn thay đổi.</div>
                        </div>
                    <?php endif; ?>
                    
                    <div class="mb-3">
                        <label for="role" class="form-label">Vai trò</label>
                        <select id="role" name="role" class="form-control" readonly>
                            <option value="user" selected>User</option>
                        </select>
                        <div class="form-text">Vai trò chỉ có thể là User và không thể thay đổi.</div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Cập nhật</button>
                    <a href="/views/admin.php?page=manage_users" class="btn btn-secondary">Quay lại</a>
                </form>
            </div>
        </div>
    </div>
    <script src="../../../public/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Validate password length if password field exists
        const passwordField = document.getElementById('new_password');
        if (passwordField) {
            passwordField.addEventListener('input', function() {
                const password = this.value;
                const submitBtn = document.querySelector('button[type="submit"]');
                
                if (password.length > 0 && password.length < 6) {
                    this.classList.add('is-invalid');
                    if (!document.querySelector('.invalid-feedback')) {
                        const feedback = document.createElement('div');
                        feedback.className = 'invalid-feedback';
                        feedback.textContent = 'Mật khẩu phải có ít nhất 6 ký tự.';
                        this.parentNode.appendChild(feedback);
                    }
                    submitBtn.disabled = true;
                } else {
                    this.classList.remove('is-invalid');
                    const feedback = document.querySelector('.invalid-feedback');
                    if (feedback) {
                        feedback.remove();
                    }
                    submitBtn.disabled = false;
                }
            });
        }
        
        // Prevent form manipulation via browser tools
        document.addEventListener('DOMContentLoaded', function() {
            const roleSelect = document.getElementById('role');
            if (roleSelect) {
                // Make select readonly and prevent changes
                roleSelect.addEventListener('change', function() {
                    this.value = 'user';
                });
                
                // Remove any other options that might be added via browser tools
                setInterval(function() {
                    const options = roleSelect.querySelectorAll('option');
                    if (options.length > 1) {
                        options.forEach(function(option, index) {
                            if (index > 0) {
                                option.remove();
                            }
                        });
                    }
                    roleSelect.value = 'user';
                }, 1000);
            }
        });
    </script>
</body>
</html>