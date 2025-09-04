<?php
require 'auth_processing.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['userid']) || !isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

$message = '';
$messageType = '';

// Xử lý form đăng bài
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $company_name = trim($_POST['company_name']);
    $job_title = trim($_POST['job_title']);
    $job_description = trim($_POST['job_description']);
    $salary = trim($_POST['salary']);
    $contact_phone = trim($_POST['contact_phone']);
    $contact_email = trim($_POST['contact_email']);
    $company_address = trim($_POST['company_address']);
    $user_id = $_SESSION['userid'];

    // Validate dữ liệu
    $errors = [];
    if (empty($company_name)) $errors[] = "Tên công ty không được trống";
    if (empty($job_title)) $errors[] = "Vị trí tuyển dụng không được trống";
    if (empty($job_description)) $errors[] = "Mô tả công việc không được trống";
    if (empty($contact_phone)) $errors[] = "Số điện thoại không được trống";
    if (empty($contact_email)) $errors[] = "Email liên hệ không được trống";
    if (!filter_var($contact_email, FILTER_VALIDATE_EMAIL)) $errors[] = "Email không hợp lệ";

    if (empty($errors)) {
        try {
            $normal_user_id = null;
            $google_user_id = null;

            // Kiểm tra user thuộc bảng nào
            $checkGoogleStmt = $pdo->prepare("SELECT id FROM google_users WHERE id = ?");
            $checkGoogleStmt->execute([$user_id]);

            if ($checkGoogleStmt->fetch()) {
                $google_user_id = $user_id;
            } else {
                $normal_user_id = $user_id;
            }

            $stmt = $pdo->prepare("
                INSERT INTO recruitments (
                    normal_user_id, google_user_id, company_name, job_title, job_description, 
                    salary, contact_phone, contact_email, company_address
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $normal_user_id,
                $google_user_id,
                $company_name,
                $job_title,
                $job_description,
                $salary,
                $contact_phone,
                $contact_email,
                $company_address
            ]);

            $message = "Đăng bài tuyển dụng thành công! <a href='my-jobs.php' class='alert-link'>Xem tin đăng của bạn</a>";
            $messageType = "success";

            $_POST = []; // Reset form
        } catch (Exception $e) {
            $message = "Có lỗi xảy ra: " . $e->getMessage();
            $messageType = "error";
        }
    } else {
        $message = implode("<br>", $errors);
        $messageType = "error";
    }
}

// Lấy danh sách bài đăng của user hiện tại
if (isset($_SESSION['login_type']) && $_SESSION['login_type'] === 'google') {
    $stmt = $pdo->prepare("SELECT * FROM recruitments WHERE google_user_id = ? ORDER BY created_at DESC");
} else {
    $stmt = $pdo->prepare("SELECT * FROM recruitments WHERE normal_user_id = ? ORDER BY created_at DESC");
}
$stmt->execute([$_SESSION['userid']]);
$userRecruitments = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Insigtful</title>
    <link rel="icon" type="image/png" href="/public/images/logo.png">
    <link rel="stylesheet" href="../public/css/style.css">
    <link rel="stylesheet" href="../public/css/auth.css">
    <link rel="stylesheet" href="../public/css/interactive.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://accounts.google.com/gsi/client" async defer></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .card { box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075); border: 1px solid rgba(0, 0, 0, 0.125); border-radius: 0.375rem; }
        .card-header { border-bottom: 1px solid rgba(0, 0, 0, 0.125); border-radius: 0.375rem 0.375rem 0 0; }
        .form-control:focus { border-color: #80bdff; box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25); }
        .btn-primary { background-color: #007bff; border-color: #007bff; }
        .btn-primary:hover { background-color: #0056b3; border-color: #0056b3; }
        .text-danger { color: #dc3545 !important; }
        .alert-dismissible .btn-close { position: absolute; top: 0.5rem; right: 0.5rem; }
    </style>
</head>

<body>
    <?php include '../views/header.php'; ?>
    <div class="container mt-4">
        <div class="row">
            <!-- Form đăng tin -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4><i class="fas fa-briefcase"></i> Đăng tin tuyển dụng</h4>
                    </div>
                    <div class="card-body">
                        <?php if ($message): ?>
                            <div class="alert alert-<?php echo $messageType === 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show">
                                <?php echo $message; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <form method="POST">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Tên công ty <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="company_name" required
                                        value="<?php echo isset($_POST['company_name']) ? htmlspecialchars($_POST['company_name']) : ''; ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Vị trí tuyển dụng <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="job_title" required
                                        value="<?php echo isset($_POST['job_title']) ? htmlspecialchars($_POST['job_title']) : ''; ?>">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Mô tả công việc <span class="text-danger">*</span></label>
                                <textarea class="form-control" name="job_description" rows="6" required><?php echo isset($_POST['job_description']) ? htmlspecialchars($_POST['job_description']) : ''; ?></textarea>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Mức lương</label>
                                    <input type="text" class="form-control" name="salary" placeholder="VD: 10-15 triệu"
                                        value="<?php echo isset($_POST['salary']) ? htmlspecialchars($_POST['salary']) : ''; ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Số điện thoại liên hệ <span class="text-danger">*</span></label>
                                    <input type="tel" class="form-control" name="contact_phone" required
                                        value="<?php echo isset($_POST['contact_phone']) ? htmlspecialchars($_POST['contact_phone']) : ''; ?>">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Email liên hệ <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" name="contact_email" required
                                    value="<?php echo isset($_POST['contact_email']) ? htmlspecialchars($_POST['contact_email']) : ''; ?>">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Địa chỉ công ty</label>
                                <textarea class="form-control" name="company_address" rows="3"><?php echo isset($_POST['company_address']) ? htmlspecialchars($_POST['company_address']) : ''; ?></textarea>
                            </div>

                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane"></i> Đăng tin tuyển dụng
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Tin đăng của user -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h5><i class="fas fa-list"></i> Tin đăng của bạn</h5>
                    </div>
                    <div class="card-body" style="max-height: 600px; overflow-y: auto;">
                        <?php if (empty($userRecruitments)): ?>
                            <p class="text-muted">Bạn chưa đăng tin tuyển dụng nào.</p>
                        <?php else: ?>
                            <?php foreach ($userRecruitments as $job): ?>
                                <div class="card mb-3">
                                    <div class="card-body p-3">
                                        <h6 class="card-title"><?php echo htmlspecialchars($job['job_title']); ?></h6>
                                        <p class="card-text">
                                            <small class="text-muted">
                                                <i class="fas fa-building"></i> <?php echo htmlspecialchars($job['company_name']); ?><br>
                                                <i class="fas fa-calendar"></i> <?php echo date('d/m/Y', strtotime($job['created_at'])); ?>
                                            </small>
                                        </p>
                                        <span class="badge bg-<?php echo $job['status'] === 'active' ? 'success' : 'secondary'; ?>">
                                            <?php echo $job['status'] === 'active' ? 'Đang hoạt động' : 'Không hoạt động'; ?>
                                        </span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include '../views/footer.php'; ?>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="../public/js/auth.js"></script>
    <script src="../public/js/sidebar.js"></script>
    <script src="../public/js/header.js"></script>
    <script src="../public/js/navbar.js"></script>
    <script src="../public/js/interactive-helpers.js"></script>
    <script src="../public/js/interactive-system.js"></script>
</body>
</html>
