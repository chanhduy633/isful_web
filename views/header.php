<!-- Header -->
<section class="header">
    <div class="container-custom">
        <nav class="navbar navbar-expand-lg navbar-light border-bottom">

            <a class="navbar-brand" href="#"><img class="img-fluid" src="../public/images/logo.png" alt="logo"></a>

            <ul class="navbar-nav me-auto">
                <li><a class="nav-link " href="index.php"><i class="fas fa-home"></i> Home</a></li>
                <li><a class="nav-link " href="toppick.php"><i class="fas fa-user fa-fire"></i> Top pick</a></li>
                <li><a class="nav-link " href="#"><i class="fas fa-comments"></i> Insight</a></li>
                <li><a class="nav-link " href="#"><i class="fas fa-briefcase"></i> Tuyển Dụng</a></li>
                <li><a class="nav-link " href="#"><i class="fas fa-clock"></i> Study </a></li>

            </ul>


            <div class="navbar-nav ms-auto">
                <div class="search-login right">
                    <i class="fas fa-search"></i>

                </div>
                <?php if (isset($_SESSION['userid'])): ?>
                    <!-- User is logged in -->
                    <div class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                            <div class="user-info">
                                <?php if (isset($_SESSION['picture']) && !empty($_SESSION['picture'])): ?>
                                    <img src="<?php echo htmlspecialchars($_SESSION['picture']); ?>" class="user-avatar" alt="Avatar" referrerpolicy="no-referrer">
                                <?php else: ?>
                                    <div class="user-avatar bg-primary d-flex align-items-center justify-content-center text-white">
                                        <i class="fas fa-user"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="#"><i class="fas fa-user me-2"></i>Thông tin cá nhân</a></li>
                            <li><a class="dropdown-item" href="#"><i class="fas fa-cog me-2"></i>Cài đặt</a></li>
                            <?php if ($_SESSION['role'] === 'admin'): ?>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item" href="/views/admin.php?page=category"><i class="fas fa-shield-alt me-2"></i>Quản trị</a></li>
                            <?php endif; ?>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><a class="dropdown-item" href="?action=logout"><i class="fas fa-sign-out-alt me-2"></i>Đăng xuất</a></li>
                        </ul>
                    </div>
                <?php else: ?>
                    <!-- User is not logged in -->
                    <div class="auth-buttons">
                        <button class="btn btn-auth" data-bs-toggle="modal" data-bs-target="#loginModal">
                            Đăng nhập
                        </button>
                        <span>|</span>
                        <button class="btn btn-auth" data-bs-toggle="modal" data-bs-target="#registerModal">
                            Đăng ký
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </nav>
    </div>

</section>