<?php
require_once '../connect/db.php';

// Check if the user is logged in and is an admin
if (!isset($_SESSION['userid']) || $_SESSION['role'] !== 'admin') {
    header('Location: /views/admin.php?page=dashboard');
    exit();
}

// Xử lý các hành động
if (isset($_GET['action'])) {
    $action = $_GET['action'];
    $id = $_GET['id'] ?? 0;

    if ($action === 'delete' && $id) {
        try {
            $stmt = $pdo->prepare("DELETE FROM recruitments WHERE id = ?");
            $stmt->execute([$id]);
            $message = "Xóa bài tuyển dụng thành công!";
            $messageType = "success";
        } catch (Exception $e) {
            $message = "Có lỗi xảy ra: " . $e->getMessage();
            $messageType = "error";
        }
    }

    if ($action === 'toggle_status' && $id) {
        try {
            // Lấy trạng thái hiện tại
            $stmt = $pdo->prepare("SELECT status FROM recruitments WHERE id = ?");
            $stmt->execute([$id]);
            $current_status = $stmt->fetchColumn();

            // Chuyển đổi trạng thái
            $new_status = $current_status === 'active' ? 'inactive' : 'active';

            $stmt = $pdo->prepare("UPDATE recruitments SET status = ? WHERE id = ?");
            $stmt->execute([$new_status, $id]);

            $message = "Cập nhật trạng thái thành công!";
            $messageType = "success";
        } catch (Exception $e) {
            $message = "Có lỗi xảy ra: " . $e->getMessage();
            $messageType = "error";
        }
    }
}

// Lấy tham số tìm kiếm và phân trang
$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? '';
$page = max(1, (int)($_GET['p'] ?? 1));
$limit = 10;
$offset = ($page - 1) * $limit;

$whereClause = [];
$params = [];

if (!empty($search)) {
    $whereClause[] = "(r.job_title LIKE ? OR r.company_name LIKE ? OR u.username LIKE ? OR g.name LIKE ?)";
    $searchParam = "%$search%";
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
}

if (!empty($status)) {
    $whereClause[] = "r.status = ?";
    $params[] = $status;
}

$whereSQL = !empty($whereClause) ? "WHERE " . implode(" AND ", $whereClause) : "";

// Đếm tổng số bản ghi
$countQuery = "SELECT COUNT(*) 
               FROM recruitments r 
               LEFT JOIN users u ON r.user_id = u.id 
               LEFT JOIN google_users g ON r.user_id = g.id 
               $whereSQL";
$countStmt = $pdo->prepare($countQuery);
$countStmt->execute($params);
$totalRecords = $countStmt->fetchColumn();
$totalPages = ceil($totalRecords / $limit);

// Lấy dữ liệu
$query = "SELECT r.*, 
                 COALESCE(u.username, g.name) as username, 
                 g.email as user_email,
                 CASE 
                    WHEN u.id IS NOT NULL THEN 'normal'
                    WHEN g.id IS NOT NULL THEN 'google'
                    ELSE 'unknown'
                 END as login_type
          FROM recruitments r 
          LEFT JOIN users u ON r.normal_user_id = u.id
LEFT JOIN google_users g ON r.google_user_id = g.id
          $whereSQL 
          ORDER BY r.created_at DESC 
          LIMIT ? OFFSET ?";

$params[] = $limit;
$params[] = $offset;

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$recruitments = $stmt->fetchAll();
?>
    <link rel="stylesheet" href="../public/css/bootstrap.min.css" type="text/css">

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Quản lý tuyển dụng</h1>
    <div>
        <span class="badge bg-info"><?php echo $totalRecords; ?> bài đăng</span>
    </div>
</div>

<?php if (isset($message)): ?>
    <div class="alert alert-<?php echo $messageType === 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show">
        <?php echo $message; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Bộ lọc -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <input type="hidden" name="page" value="manage_recruitment">
            <div class="col-md-4">
                <input type="text" class="form-control" name="search" placeholder="Tìm kiếm..."
                    value="<?php echo htmlspecialchars($search); ?>">
            </div>
            <div class="col-md-3">
                <select class="form-select" name="status">
                    <option value="">Tất cả trạng thái</option>
                    <option value="active" <?php echo $status === 'active' ? 'selected' : ''; ?>>Hoạt động</option>
                    <option value="inactive" <?php echo $status === 'inactive' ? 'selected' : ''; ?>>Không hoạt động</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Tìm kiếm
                </button>
            </div>
            <div class="col-md-3">
                <a href="admin.php?page=manage_recruitment" class="btn btn-outline-secondary">
                    <i class="fas fa-refresh"></i> Đặt lại
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Bảng dữ liệu -->
<?php if (empty($recruitments)): ?>
    <div class="text-center py-4">
        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
        <p class="text-muted">Không có bài tuyển dụng nào.</p>
    </div>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Vị trí</th>
                    <th>Công ty</th>
                    <th>Người đăng</th>
                    <th>Loại TK</th>
                    <th>Lương</th>
                    <th>Trạng thái</th>
                    <th>Ngày đăng</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recruitments as $job): ?>
                    <tr>
                        <td><?php echo $job['id']; ?></td>
                        <td>
                            <strong><?php echo htmlspecialchars($job['job_title']); ?></strong>
                        </td>
                        <td><?php echo htmlspecialchars($job['company_name']); ?></td>
                        <td>
                            <i class="fas fa-user"></i> <?php echo htmlspecialchars($job['username']); ?><br>
                            <small class="text-muted"><?php echo htmlspecialchars($job['user_email']); ?></small>
                        </td>
                        <td>
                            <?php if ($job['login_type'] === 'google'): ?>
                                <span class="badge bg-info">Google</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Thường</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php echo $job['salary'] ? htmlspecialchars($job['salary']) : '<span class="text-muted">Chưa có</span>'; ?>
                        </td>
                        <td>
                            <span class="badge bg-<?php echo $job['status'] === 'active' ? 'success' : 'secondary'; ?>">
                                <?php echo $job['status'] === 'active' ? 'Hoạt động' : 'Không hoạt động'; ?>
                            </span>
                        </td>
                        <td>
                            <?php echo date('d/m/Y H:i', strtotime($job['created_at'])); ?>
                        </td>
                        <td>
                            <div class="btn-group w-100" role="group">
                                <button type="button" class="btn btn-sm btn-outline-info"
                                    onclick="viewJob(<?php echo $job['id']; ?>)" title="Xem chi tiết">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <a href="admin.php?page=manage_recruitment&action=toggle_status&id=<?php echo $job['id']; ?>"
                                    class="btn btn-sm btn-outline-warning"
                                    onclick="return confirm('Bạn có muốn thay đổi trạng thái bài đăng này?')"
                                    title="Thay đổi trạng thái">
                                    <i class="fas fa-toggle-on"></i>
                                </a>
                                <a href="admin.php?page=manage_recruitment&action=delete&id=<?php echo $job['id']; ?>"
                                    class="btn btn-sm btn-outline-danger"
                                    onclick="return confirm('Bạn có chắc muốn xóa bài đăng này?')"
                                    title="Xóa">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Phân trang -->
    <?php if ($totalPages > 1): ?>
        <nav aria-label="Page navigation">
            <ul class="pagination justify-content-center">
                <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=manage_recruitment&p=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status); ?>">
                            Trước
                        </a>
                    </li>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                        <a class="page-link" href="?page=manage_recruitment&p=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status); ?>">
                            <?php echo $i; ?>
                        </a>
                    </li>
                <?php endfor; ?>

                <?php if ($page < $totalPages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=manage_recruitment&p=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status); ?>">
                            Sau
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    <?php endif; ?>
<?php endif; ?>

<!-- Modal chi tiết công việc -->
<div class="modal fade" id="jobDetailModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Chi tiết công việc</h5>
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

<style>
    .table th {
        border-top: none;
        font-weight: 600;
    }

    .btn-group .btn {
        margin-right: 2px;
    }

    .btn-group .btn:last-child {
        margin-right: 0;
    }

    .badge {
        font-size: 0.75em;
    }

    .card {
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }

    .table-responsive {
        border-radius: 0.375rem;
    }

    .pagination .page-link {
        color: #007bff;
    }

    .pagination .page-item.active .page-link {
        background-color: #007bff;
        border-color: #007bff;
    }
</style>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    

<script>
    async function viewJob(jobId) {
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
            const response = await fetch(`/views/job-detail-ajax.php?id=${jobId}`);
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
                        <p><strong><i class="fas fa-user"></i> Người đăng:</strong> ${job.username}</p>
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