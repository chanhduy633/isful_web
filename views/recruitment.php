<?php
require 'auth_processing.php'; // xử lý login, register, logout

// Lấy tham số tìm kiếm
$search  = $_GET['search'] ?? '';
$salary = $_GET['salary'] ?? '';
$page    = max(1, (int)($_GET['page'] ?? 1));
$limit   = 8;
$offset  = ($page - 1) * $limit;

// Xây dựng query điều kiện
$whereClause = ["status = 'active'"];
$params = [];

if (!empty($search)) {
    $whereClause[] = "(job_title LIKE ? OR job_description LIKE ?)";
    $searchParam = "%$search%";
    $params[] = $searchParam;
    $params[] = $searchParam;
}

if (!empty($salary)) {
    $whereClause[] = "salary LIKE ?";
    $params[] = "%$salary%";
}

$whereSQL = "WHERE " . implode(" AND ", $whereClause);

// Đếm tổng số bản ghi
$countQuery = "SELECT COUNT(*) FROM recruitments $whereSQL";
$countStmt  = $pdo->prepare($countQuery);
$countStmt->execute($params);
$totalRecords = $countStmt->fetchColumn();
$totalPages   = ceil($totalRecords / $limit);

// Lấy dữ liệu việc làm
$query = "SELECT r.*,
    CASE 
        WHEN u.id IS NOT NULL THEN u.username
        WHEN g.id IS NOT NULL THEN g.name
        ELSE 'Người dùng không xác định'
    END AS display_name,
    COALESCE(u.username, g.name) AS username,
    CASE 
        WHEN u.id IS NOT NULL THEN 'normal'
        WHEN g.id IS NOT NULL THEN 'google'
        ELSE 'unknown'
    END AS login_type
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
$jobs = $stmt->fetchAll();

// Lấy danh sách salary để filter
$salaryStmt = $pdo->prepare("SELECT DISTINCT salary FROM recruitments WHERE status = 'active' AND salary IS NOT NULL AND salary <> '' ORDER BY salary");
$salaryStmt->execute();
$salaries = $salaryStmt->fetchAll();
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
            transform: translateY(-5px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
        }

        .display-4 {
            font-weight: 300;
        }

        .card {
            border-radius: 0.75rem;
        }

        .btn {
            border-radius: 0.5rem;
        }



        .pagination .page-link {
            border-radius: 0.5rem;
            margin: 0 0.125rem;
            color: #007bff;
        }

        .pagination .page-item.active .page-link {
            background-color: #007bff;
            border-color: #007bff;
        }

        .modal-content {
            border-radius: 1rem;
            border: none;
            box-shadow: 0 1rem 3rem rgba(0, 0, 0, 0.175);
        }

        .form-control,
        .form-select {
            border-radius: 0.5rem;
        }

        .lead {
            font-size: 1.125rem;
            font-weight: 300;
        }

        .card-title {
            font-size: 1.1rem;
            font-weight: 600;
        }

        .card-subtitle {
            font-size: 0.95rem;
        }
    </style>
</head>

<body>
    <?php
    include '../views/header.php';
    include '../views/login.php';
    ?>
    <div class="container-custom mt-4">
        <!-- Header và search -->
        <div class="row mb-4">
            <div class="col-8">
                <!-- Bộ lọc tìm kiếm -->
                <div class="card shadow-sm">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-5">
                                <label class="form-label">Tìm kiếm theo vị trí hoặc mô tả</label>
                                <input type="text" class="form-control" name="search"
                                    placeholder="VD: PHP Developer, Marketing..."
                                    value="<?php echo htmlspecialchars($search); ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Mức lương</label>
                                <select class="form-select" name="salary">
                                    <option value="">Tất cả mức lương</option>
                                    <?php foreach ($salaries as $sal): ?>
                                        <option value="<?php echo htmlspecialchars($sal['salary']); ?>"
                                            <?php echo $salary === $sal['salary'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($sal['salary']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">&nbsp;</label>
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search"></i> Tìm kiếm
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-4">
                <div class="text-center ">
                    <h1 class=" ">
                        <i class="fas fa-briefcase text-primary"></i> Việc làm
                    </h1>
                    <p class="lead text-muted">Khám phá các cơ hội nghề nghiệp hấp dẫn</p>

                    <!-- Nút đăng tin nếu đã đăng nhập -->
                    <?php if (isset($_SESSION['userid'])): ?>
                        <div class="">
                            <a href="post-job.php" class="btn btn-success ">
                                <i class="fas fa-plus"></i> Đăng tin tuyển dụng
                            </a>
                            <a href="my-jobs.php" class="btn btn-outline-info ">
                                <i class="fas fa-list"></i> Tin đăng của tôi
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="mb-3">
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#loginModal">
                                <i class="fas fa-sign-in-alt"></i> Đăng nhập để đăng tin tuyển dụng
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Thống kê -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <h4>
                        <i class="fas fa-chart-bar"></i>
                        Tìm thấy <?php echo $totalRecords; ?> việc làm
                        <?php if (!empty($search) || !empty($company)): ?>
                            <small class="text-muted">
                                <?php if (!empty($search)): ?>
                                    cho "<?php echo htmlspecialchars($search); ?>"
                                <?php endif; ?>
                                <?php if (!empty($company)): ?>
                                    tại <?php echo htmlspecialchars($company); ?>
                                <?php endif; ?>
                            </small>
                        <?php endif; ?>
                    </h4>
                    <?php if (!empty($search) || !empty($company)): ?>
                        <a href="recruitment.php" class="btn btn-outline-secondary">
                            <i class="fas fa-times"></i> Xóa bộ lọc
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Danh sách việc làm -->
        <div class="row">
            <?php if (empty($jobs)): ?>
                <div class="col-8">
                    <div class="text-center py-5">
                        <i class="fas fa-search fa-3x text-muted mb-3"></i>
                        <h4 class="text-muted">Không tìm thấy việc làm phù hợp</h4>
                        <p class="text-muted">Thử thay đổi từ khóa tìm kiếm hoặc bộ lọc</p>
                        <a href="recruitment.php" class="btn btn-primary">Xem tất cả việc làm</a>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($jobs as $job): ?>
                    <div class="col-md-8 col-lg-8 mb-4">
                        <div class="card h-100 shadow-sm job-card">
                            <div class="card-body d-flex flex-column">
                                <div class="mb-3">
                                    <h5 class="card-title text-primary mb-2">
                                        <?php echo htmlspecialchars($job['job_title']); ?>
                                    </h5>
                                    <h6 class="card-subtitle text-muted mb-2">
                                        <i class="fas fa-building"></i>
                                        <?php echo htmlspecialchars($job['company_name']); ?>
                                    </h6>
                                    <small class="text-muted">
                                        <i class="fas fa-user"></i> Đăng bởi: <?php echo htmlspecialchars($job['username']); ?>
                                    </small>
                                </div>

                                <div class="mb-3 flex-grow-1">
                                    <p class="card-text text-muted small">
                                        <?php
                                        $description = htmlspecialchars($job['job_description']);
                                        echo strlen($description) > 150 ? substr($description, 0, 150) . '...' : $description;
                                        ?>
                                    </p>
                                </div>

                                <div class="mb-3">
                                    <?php if ($job['salary']): ?>
                                        <div class="d-flex align-items-center mb-2">
                                            <i class="fas fa-money-bill-wave text-success me-2"></i>
                                            <span class="fw-bold text-success">
                                                <?php echo htmlspecialchars($job['salary']); ?>
                                            </span>
                                        </div>
                                    <?php endif; ?>

                                    <?php if ($job['company_address']): ?>
                                        <div class="d-flex align-items-center mb-2">
                                            <i class="fas fa-map-marker-alt text-info me-2"></i>
                                            <span class="small text-muted">
                                                <?php
                                                $address = htmlspecialchars($job['company_address']);
                                                echo strlen($address) > 50 ? substr($address, 0, 50) . '...' : $address;
                                                ?>
                                            </span>
                                        </div>
                                    <?php endif; ?>

                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-calendar text-secondary me-2"></i>
                                        <span class="small text-muted">
                                            <?php echo date('d/m/Y', strtotime($job['created_at'])); ?>
                                        </span>
                                    </div>
                                </div>

                                <div class="mt-auto">
                                    <button class="btn btn-primary btn-sm" onclick="showJobDetail(<?php echo $job['id']; ?>)">
                                        <i class="fas fa-eye"></i> Xem chi tiết
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Phân trang -->
        <?php if ($totalPages > 1): ?>
            <nav aria-label="Job pagination" class="mt-4">
                <ul class="pagination justify-content-center">
                    <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&salary=<?php echo urlencode($salary); ?>">
                                <i class="fas fa-chevron-left"></i> Trước
                            </a>
                        </li>
                    <?php endif; ?>

                    <?php
                    $startPage = max(1, $page - 2);
                    $endPage = min($totalPages, $page + 2);

                    if ($startPage > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=1&search=<?php echo urlencode($search); ?>&salary=<?php echo urlencode($salary); ?>">1</a>
                        </li>
                        <?php if ($startPage > 2): ?>
                            <li class="page-item disabled"><span class="page-link">...</span></li>
                        <?php endif; ?>
                    <?php endif; ?>

                    <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&salary=<?php echo urlencode($salary); ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                    <?php endfor; ?>

                    <?php if ($endPage < $totalPages): ?>
                        <?php if ($endPage < $totalPages - 1): ?>
                            <li class="page-item disabled"><span class="page-link">...</span></li>
                        <?php endif; ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $totalPages; ?>&search=<?php echo urlencode($search); ?>&salary=<?php echo urlencode($salary); ?>"><?php echo $totalPages; ?></a>
                        </li>
                    <?php endif; ?>

                    <?php if ($page < $totalPages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&salary=<?php echo urlencode($salary); ?>">
                                Sau <i class="fas fa-chevron-right"></i>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        <?php endif; ?>
    </div>

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



    <script>
        async function showJobDetail(jobId) {
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
                        <small class="text-muted">
                            <i class="fas fa-user"></i> Đăng bởi: ${job.username}
                            ${job.user_type === 'google' ? '<span class="badge bg-info ms-1" style="font-size: 0.6em;">Google</span>' : ''}
                        </small>
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