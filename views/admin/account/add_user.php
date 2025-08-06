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

$success = false;
$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $role = 'user'; // Force role to be 'user' only
    $email = trim($_POST['email']);

    // Validate input
    if (empty($username) || empty($password)) {
        $error = 'Tên đăng nhập và mật khẩu không được để trống!';
    } else {
        // Check if username already exists
        $check_stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
        $check_stmt->execute([$username]);
        
        if ($check_stmt->fetchColumn() > 0) {
            $error = 'Tên đăng nhập đã tồn tại!';
        } else {
            // Check if email already exists (if provided)
            if (!empty($email)) {
                $email_check = $pdo->prepare("SELECT COUNT(*) FROM google_users WHERE email = ?");
                $email_check->execute([$email]);
                
                if ($email_check->fetchColumn() > 0) {
                    $error = 'Email đã được sử dụng!';
                }
            }
            
            if (empty($error)) {
                $hashed_password = password_hash($password, PASSWORD_BCRYPT);
                
                // Insert the new user (always as 'user' role, not system admin)
                $stmt = $pdo->prepare("INSERT INTO users (username, password, role, is_system_admin) VALUES (?, ?, ?, 0)");
                $stmt->execute([$username, $hashed_password, $role]);
                
                $success = true;
            }
        }
    }
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
        
        <?php if ($success): ?>
            <div class="alert alert-success">
                Tài khoản đã được tạo thành công!
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
        
        <form method="post" action="">
            <div class="mb-3">
                <label for="username" class="form-label">Tên đăng nhập <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="username" name="username" 
                       value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" required>
            </div>
            
            <div class="mb-3">
                <label for="password" class="form-label">Mật khẩu <span class="text-danger">*</span></label>
                <input type="password" class="form-control" id="password" name="password" required>
                <div class="form-text">Mật khẩu phải có ít nhất 6 ký tự.</div>
            </div>
            
            <div class="mb-3">
                <label for="email" class="form-label">Email (tùy chọn)</label>
                <input type="email" class="form-control" id="email" name="email" 
                       value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
            </div>
            
            <div class="mb-3">
                <label for="role" class="form-label">Vai trò</label>
                <select id="role" name="role" class="form-control" readonly>
                    <option value="user" selected>User</option>
                </select>
                <div class="form-text">Chỉ có thể tạo tài khoản với vai trò User.</div>
            </div>
            
            <button type="submit" class="btn btn-primary">Thêm</button>
            <a href="/views/admin.php?page=manage_users" class="btn btn-secondary">Quay lại</a>
        </form>
    </div>
    <script src="../../../public/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Validate password length
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            const submitBtn = document.querySelector('button[type="submit"]');
            
            if (password.length < 6) {
                this.classList.add('is-invalid');
                submitBtn.disabled = true;
            } else {
                this.classList.remove('is-invalid');
                submitBtn.disabled = false;
            }
        });
        
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