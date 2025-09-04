<?php
require 'auth_processing.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['userid']) || !isset($_SESSION['username']) || !isset($_SESSION['login_type'])) {
    header('Location: login.php');
    exit();
}

$message = '';
$messageType = '';

// Xác định cột user theo loại đăng nhập
$userId = $_SESSION['userid'];
$loginType = $_SESSION['login_type']; // 'normal' hoặc 'google'

$userColumn = $loginType === 'google' ? 'google_user_id' : 'normal_user_id';

// ================== XỬ LÝ XÓA ==================
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $jobId = $_GET['id'];

    try {
        $stmt = $pdo->prepare("SELECT id FROM recruitments WHERE id = ? AND $userColumn = ?");
        $stmt->execute([$jobId, $userId]);

        if ($stmt->fetch()) {
            $deleteStmt = $pdo->prepare("DELETE FROM recruitments WHERE id = ? AND $userColumn = ?");
            $deleteStmt->execute([$jobId, $userId]);

            $message = "Xóa tin tuyển dụng thành công!";
            $messageType = "success";
        } else {
            $message = "Bạn không có quyền xóa tin đăng này!";
            $messageType = "error";
        }
    } catch (Exception $e) {
        $message = "Có lỗi xảy ra: " . $e->getMessage();
        $messageType = "error";
    }
}

// ================== XỬ LÝ ĐỔI TRẠNG THÁI ==================
if (isset($_GET['action']) && $_GET['action'] === 'toggle' && isset($_GET['id'])) {
    $jobId = $_GET['id'];

    try {
        $stmt = $pdo->prepare("SELECT status FROM recruitments WHERE id = ? AND $userColumn = ?");
        $stmt->execute([$jobId, $userId]);
        $currentStatus = $stmt->fetchColumn();

        if ($currentStatus !== false) {
            $newStatus = $currentStatus === 'active' ? 'inactive' : 'active';
            $updateStmt = $pdo->prepare("UPDATE recruitments SET status = ? WHERE id = ? AND $userColumn = ?");
            $updateStmt->execute([$newStatus, $jobId, $userId]);

            $statusText = $newStatus === 'active' ? 'kích hoạt' : 'tạm ngừng';
            $message = "Đã $statusText tin tuyển dụng thành công!";
            $messageType = "success";
        } else {
            $message = "Bạn không có quyền thay đổi tin đăng này!";
            $messageType = "error";
        }
    } catch (Exception $e) {
        $message = "Có lỗi xảy ra: " . $e->getMessage();
        $messageType = "error";
    }
}

// ================== LẤY DANH SÁCH JOB CỦA USER ==================
$stmt = $pdo->prepare("SELECT * FROM recruitments WHERE $userColumn = ? ORDER BY created_at DESC");
$stmt->execute([$userId]);
$userJobs = $stmt->fetchAll();

// Thống kê
$activeJobs = array_filter($userJobs, fn($job) => $job['status'] === 'active');
$inactiveJobs = array_filter($userJobs, fn($job) => $job['status'] === 'inactive');
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
        .job-card {
            transition: transform 0.2s, box-shadow 0.2s;
            border: none;
        }

        .job-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
        }

        .card {
            border-radius: 0.75rem;
        }

        .btn {
            border-radius: 0.5rem;
        }

        .display-5 {
            font-weight: 300;
        }

        .badge.fs-6 {
            font-size: 0.875rem !important;
        }

        .btn-group .btn {
            flex: 1;
        }

        .breadcrumb-item+.breadcrumb-item::before {
            content: ">";
        }

        .alert {
            border-radius: 0.75rem;
        }

        .modal-content {
            border-radius: 1rem;
            border: none;
            box-shadow: 0 1rem 3rem rgba(0, 0, 0, 0.175);
        }

        .card-title {
            font-size: 1.1rem;
            font-weight: 600;
        }

        .card-subtitle {
            font-size: 0.95rem;
        }

        .text-success {
            color: #28a745 !important;
        }

        .bg-primary {
            background-color: #007bff !important;
        }

        .bg-success {
            background-color: #28a745 !important;
        }

        .bg-warning {
            background-color: #ffc107 !important;
        }

        .bg-info {
            background-color: #17a2b8 !important;
        }
    </style>

</head>

<body>
    <?php
    include '../views/header.php';
    include '../views/login.php';
    ?>
    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <!-- Header -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h1 class="display-5 mb-2">
                            <i class="fas fa-briefcase text-primary"></i> Tin tuyển dụng của tôi
                        </h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="index.php">Trang chủ</a></li>
                                <li class="breadcrumb-item"><a href="recruitment.php">Việc làm</a></li>
                                <li class="breadcrumb-item active">Tin đăng của tôi</li>
                            </ol>
                        </nav>
                    </div>
                    <div>
                        <a href="post-job.php" class="btn btn-success btn-lg">
                            <i class="fas fa-plus"></i> Đăng tin mới
                        </a>
                    </div>
                </div>

                <!-- Thống kê -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card text-center bg-primary text-white">
                            <div class="card-body">
                                <h3><?php echo count($userJobs); ?></h3>
                                <p class="mb-0">Tổng tin đăng</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center bg-success text-white">
                            <div class="card-body">
                                <h3><?php echo count($activeJobs); ?></h3>
                                <p class="mb-0">Đang hoạt động</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center bg-warning text-white">
                            <div class="card-body">
                                <h3><?php echo count($inactiveJobs); ?></h3>
                                <p class="mb-0">Tạm ngừng</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center bg-info text-white">
                            <div class="card-body">
                                <h3><?php echo date('d/m'); ?></h3>
                                <p class="mb-0">Hôm nay</p>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if ($message): ?>
                    <script>
                        document.addEventListener("DOMContentLoaded", function() {
                            const toastContainer = document.getElementById("toastContainer");
                            const toastEl = document.createElement("div");

                            toastEl.className = "toast align-items-center text-bg-<?php echo $messageType === 'success' ? 'success' : 'danger'; ?> border-0";
                            toastEl.setAttribute("role", "alert");
                            toastEl.setAttribute("aria-live", "assertive");
                            toastEl.setAttribute("aria-atomic", "true");

                            toastEl.innerHTML = `
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-triangle'; ?>"></i>
                        <?php echo $message; ?>
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            `;

                            toastContainer.appendChild(toastEl);

                            const toast = new bootstrap.Toast(toastEl, {
                                delay: 4000
                            });
                            toast.show();
                        });
                    </script>
                <?php endif; ?>


                <!-- Danh sách tin đăng -->
                <?php if (empty($userJobs)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-folder-open fa-5x text-muted mb-4"></i>
                        <h3 class="text-muted mb-3">Chưa có tin tuyển dụng nào</h3>
                        <p class="text-muted mb-4">Hãy tạo tin đăng đầu tiên để tìm kiếm nhân tài cho công ty bạn</p>
                        <a href="post-job.php" class="btn btn-success btn-lg">
                            <i class="fas fa-plus"></i> Đăng tin tuyển dụng ngay
                        </a>
                    </div>
                <?php else: ?>
                    <div class="row">
                        <?php foreach ($userJobs as $job): ?>
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card h-100 shadow-sm job-card">
                                    <div class="card-body d-flex flex-column">
                                        <!-- Header card -->
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <span class="badge bg-<?php echo $job['status'] === 'active' ? 'success' : 'secondary'; ?> fs-6">
                                                <?php echo $job['status'] === 'active' ? 'Đang hoạt động' : 'Tạm ngừng'; ?>
                                            </span>
                                            <small class="text-muted">
                                                <i class="fas fa-calendar"></i>
                                                <?php echo date('d/m/Y', strtotime($job['created_at'])); ?>
                                            </small>
                                        </div>

                                        <!-- Thông tin công việc -->
                                        <div class="mb-3 flex-grow-1">
                                            <h5 class="card-title text-primary mb-2">
                                                <?php echo htmlspecialchars($job['job_title']); ?>
                                            </h5>
                                            <h6 class="card-subtitle text-muted mb-2">
                                                <i class="fas fa-building"></i>
                                                <?php echo htmlspecialchars($job['company_name']); ?>
                                            </h6>

                                            <?php if ($job['salary']): ?>
                                                <p class="text-success mb-2">
                                                    <i class="fas fa-money-bill-wave"></i>
                                                    <strong><?php echo htmlspecialchars($job['salary']); ?></strong>
                                                </p>
                                            <?php endif; ?>

                                            <p class="card-text text-muted small">
                                                <?php
                                                $description = htmlspecialchars($job['job_description']);
                                                echo strlen($description) > 120 ? substr($description, 0, 120) . '...' : $description;
                                                ?>
                                            </p>
                                        </div>

                                        <!-- Thông tin liên hệ -->
                                        <div class="mb-3">
                                            <small class="text-muted">
                                                <i class="fas fa-phone"></i> <?php echo htmlspecialchars($job['contact_phone']); ?><br>
                                                <i class="fas fa-envelope"></i> <?php echo htmlspecialchars($job['contact_email']); ?>
                                            </small>
                                        </div>

                                        <!-- Nút hành động -->
                                        <div class="mt-auto">
                                            <div class="btn-group w-100" role="group">
                                                <button class="btn btn-outline-info btn-sm" onclick="viewJobDetail(<?php echo $job['id']; ?>)" title="Xem chi tiết">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <a href="javascript:void(0);"
                                                    class="btn btn-outline-<?php echo $job['status'] === 'active' ? 'warning' : 'success'; ?> btn-sm"
                                                    onclick="confirmAction(
       'Bạn có muốn <?php echo $job['status'] === 'active' ? 'tạm ngừng' : 'kích hoạt'; ?> tin đăng này?',
       '?action=toggle&id=<?php echo $job['id']; ?>',
       '<?php echo $job['status'] === 'active' ? 'warning' : 'success'; ?>'
   )"
                                                    title="<?php echo $job['status'] === 'active' ? 'Tạm ngừng' : 'Kích hoạt'; ?>">
                                                    <i class="fas fa-<?php echo $job['status'] === 'active' ? 'pause' : 'play'; ?>"></i>
                                                </a>

                                                <a href="javascript:void(0);"
                                                    class="btn btn-outline-danger btn-sm"
                                                    onclick="confirmAction(
       'Bạn có chắc chắn muốn xóa tin đăng này? Hành động này <b>không thể hoàn tác</b>!',
       '?action=delete&id=<?php echo $job['id']; ?>',
       'danger'
   )"
                                                    title="Xóa">
                                                    <i class="fas fa-trash"></i>
                                                </a>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Modal chi tiết công việc -->
    <div class="modal fade" id="jobDetailModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Chi tiết tin tuyển dụng</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="jobDetailContent">
                    <div class="text-center">
                        <div class="spinner-border" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                </div>
            </div>
        </div>
    </div>
    <div class="position-fixed top-0 end-0 p-3" style="z-index: 1100">
        <div id="toastContainer"></div>
    </div>
    <!-- Modal xác nhận -->
    <div class="modal fade" id="confirmModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-warning text-white">
                    <h5 class="modal-title"><i class="fas fa-exclamation-triangle"></i> Xác nhận</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="confirmMessage">
                    <!-- Nội dung động sẽ được chèn ở đây -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <a href="#" id="confirmYesBtn" class="btn btn-danger">Đồng ý</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        function confirmAction(message, url, type = "danger") {
            // Gán nội dung
            document.getElementById("confirmMessage").innerHTML = message;

            // Gán link hành động
            const yesBtn = document.getElementById("confirmYesBtn");
            yesBtn.href = url;

            // Đổi màu nút theo loại
            yesBtn.className = `btn btn-${type}`;
            yesBtn.textContent = "Đồng ý";

            // Hiển thị modal
            const modal = new bootstrap.Modal(document.getElementById("confirmModal"));
            modal.show();
        }

        function showToast(message, type = "info") {
            const toastContainer = document.getElementById("toastContainer");
            const toastEl = document.createElement("div");

            toastEl.className = `toast align-items-center text-bg-${type} border-0`;
            toastEl.setAttribute("role", "alert");
            toastEl.setAttribute("aria-live", "assertive");
            toastEl.setAttribute("aria-atomic", "true");

            toastEl.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">
                <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'danger' ? 'times-circle' : type === 'warning' ? 'exclamation-triangle' : 'info-circle'}"></i>
                ${message}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    `;

            toastContainer.appendChild(toastEl);

            const toast = new bootstrap.Toast(toastEl, {
                delay: 3000
            });
            toast.show();

            // Xóa toast khỏi DOM sau khi ẩn
            toastEl.addEventListener("hidden.bs.toast", () => toastEl.remove());
        }
    </script>

    <script>
        async function viewJobDetail(jobId) {
            const modal = new bootstrap.Modal(document.getElementById('jobDetailModal'));
            const content = document.getElementById('jobDetailContent');

            // Hiển thị loading
            content.innerHTML = `
        <div class="text-center">
            <div class="spinner-border" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2 text-muted">Đang tải thông tin...</p>
        </div>
    `;

            modal.show();

            try {
                const response = await fetch(`job-detail-ajax.php?id=${jobId}`);
                const data = await response.json();

                if (data.success) {
                    const job = data.job;
                    content.innerHTML = `
                <div class="row">
                    <div class="col-12 mb-3">
                        <h4 class="text-primary">${job.job_title}</h4>
                        <h6 class="text-muted"><i class="fas fa-building"></i> ${job.company_name}</h6>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        ${job.salary ? `<p><strong><i class="fas fa-money-bill-wave text-success"></i> Mức lương:</strong> ${job.salary}</p>` : ''}
                        <p><strong><i class="fas fa-calendar"></i> Ngày đăng:</strong> ${new Date(job.created_at).toLocaleDateString('vi-VN')}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong><i class="fas fa-phone text-primary"></i> Điện thoại:</strong> 
                            <a href="tel:${job.contact_phone}" class="text-decoration-none">${job.contact_phone}</a>
                        </p>
                        <p><strong><i class="fas fa-envelope text-primary"></i> Email:</strong> 
                            <a href="mailto:${job.contact_email}" class="text-decoration-none">${job.contact_email}</a>
                        </p>
                    </div>
                </div>
                
                ${job.company_address ? `
                <div class="mb-3">
                    <p><strong><i class="fas fa-map-marker-alt text-info"></i> Địa chỉ:</strong></p>
                    <p class="ps-4">${job.company_address.replace(/\n/g, '<br>')}</p>
                </div>
                ` : ''}
                
                <div class="mb-3">
                    <p><strong><i class="fas fa-file-alt text-warning"></i> Mô tả công việc:</strong></p>
                    <div class="p-3 bg-light rounded">
                        ${job.job_description.replace(/\n/g, '<br>')}
                    </div>
                </div>
            `;
                } else {
                    content.innerHTML = `
                <div class="text-center">
                    <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                    <p class="text-muted">Không thể tải thông tin công việc</p>
                </div>
            `;
                }
            } catch (error) {
                content.innerHTML = `
            <div class="text-center">
                <i class="fas fa-exclamation-triangle fa-3x text-danger mb-3"></i>
                <p class="text-muted">Có lỗi xảy ra khi tải dữ liệu</p>
            </div>
        `;
            }
        }
    </script>
    <?php include '../views/footer.php'; ?>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="../public/js/auth.js"></script>
    <script src="../public/js/sidebar.js"></script>
    <script src="../public/js/header.js"></script>
    <script src="../public/js/navbar.js"></script>
    <script src="../public/js/interactive-helpers.js"></script>
    <script src="../public/js/interactive-system.js"></script>

</body>

</html>