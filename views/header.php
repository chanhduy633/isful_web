<!-- Header with Mobile Menu -->
<section class="header">
    <div class="container-custom">
        <nav class="navbar navbar-expand-lg navbar-light border-bottom">
            <!-- Mobile Menu Toggle -->
            <button class="mobile-menu-toggle" type="button" aria-label="Toggle mobile menu">
                <i class="fas fa-bars"></i>
            </button>

            <!-- Logo -->
            <a class="navbar-brand" href="#">
                <img class="img-fluid" src="../public/images/logo.png" alt="logo">
            </a>

            <!-- Desktop Navigation -->
            <ul class="navbar-nav me-auto">
                <li><a class="nav-link" href="index.php"><i class="fas fa-home"></i> Home</a></li>
                <li><a class="nav-link" href="toppick.php"><i class="fas fa-fire"></i> Top pick</a></li>
                <li><a class="nav-link" href="#"><i class="fas fa-comments"></i> Insight</a></li>
                <li><a class="nav-link" href="#"><i class="fas fa-briefcase"></i> Tuyển Dụng</a></li>
                <li><a class="nav-link" href="#"><i class="fas fa-clock"></i> Study</a></li>
            </ul>

            <!-- Desktop Right Side -->
            <div class="navbar-nav ms-auto">
                <!-- Search -->
                <div class="search-icon-btn right">
                    <i class="fas fa-search"></i>
                    <input type="text" class="search-input" placeholder="Tìm kiếm..." />
                    <div class="search-results"></div>
                </div>
                
                <?php if (isset($_SESSION['userid'])): ?>
                    <!-- Desktop User Menu -->
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
                            <li><a class="dropdown-item" href="/views/user.php"><i class="fas fa-user me-2"></i>Thông tin cá nhân</a></li>
                            <?php if ($_SESSION['role'] === 'admin'): ?>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="/views/admin.php?page=category"><i class="fas fa-shield-alt me-2"></i>Quản trị</a></li>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="?action=logout"><i class="fas fa-sign-out-alt me-2"></i>Đăng xuất</a></li>
                        </ul>
                    </div>
                <?php else: ?>
                    <!-- Desktop Auth Buttons -->
                    <div class="auth-buttons">
                        <button class="btn btn-auth btn-in" data-bs-toggle="modal" data-bs-target="#loginModal">
                            Đăng nhập
                        </button>
                        <span>|</span>
                        <button class="btn btn-auth btn-up" data-bs-toggle="modal" data-bs-target="#registerModal">
                            Đăng ký
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </nav>
    </div>
</section>

<!-- Mobile Menu Overlay -->
<div class="mobile-menu-overlay"></div>

<!-- Mobile Menu -->
<div class="mobile-menu">
    <!-- Mobile Menu Header -->
    <div class="mobile-menu-header">
        <a class="navbar-brand" href="#">
            <img class="img-fluid" src="../public/images/logo.png" alt="logo" style="max-height: 30px;">
        </a>
        <button class="mobile-menu-close" type="button" aria-label="Close mobile menu">
            <i class="fas fa-times"></i>
        </button>
    </div>

    <!-- Mobile Menu Content -->
    <div class="mobile-menu-content">
        <!-- Navigation Links -->
        <ul class="mobile-nav-list">
            <li class="mobile-nav-item">
                <a href="index.php" class="mobile-nav-link">
                    <i class="fas fa-home"></i>
                    <span>Home</span>
                </a>
            </li>
            <li class="mobile-nav-item">
                <a href="toppick.php" class="mobile-nav-link">
                    <i class="fas fa-fire"></i>
                    <span>Top pick</span>
                </a>
            </li>
            <li class="mobile-nav-item">
                <a href="#" class="mobile-nav-link">
                    <i class="fas fa-comments"></i>
                    <span>Insight</span>
                </a>
            </li>
            <li class="mobile-nav-item">
                <a href="#" class="mobile-nav-link">
                    <i class="fas fa-briefcase"></i>
                    <span>Tuyển Dụng</span>
                </a>
            </li>
            <li class="mobile-nav-item">
                <a href="#" class="mobile-nav-link">
                    <i class="fas fa-clock"></i>
                    <span>Study</span>
                </a>
            </li>
        </ul>

        <?php if (isset($_SESSION['userid'])): ?>
            <!-- Mobile User Info -->
            <div class="mobile-user-info">
                <?php if (isset($_SESSION['picture']) && !empty($_SESSION['picture'])): ?>
                    <img src="<?php echo htmlspecialchars($_SESSION['picture']); ?>" class="mobile-user-avatar" alt="Avatar" referrerpolicy="no-referrer">
                <?php else: ?>
                    <div class="mobile-user-avatar bg-primary d-flex align-items-center justify-content-center text-white">
                        <i class="fas fa-user"></i>
                    </div>
                <?php endif; ?>
                <div class="mobile-user-details">
                    <h6><?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?></h6>
                    <small>Thành viên</small>
                </div>
            </div>

            <!-- Mobile User Menu -->
            <div class="mobile-user-menu">
                <a href="/views/user.php" class="mobile-user-link">
                    <i class="fas fa-user"></i>
                    <span>Thông tin cá nhân</span>
                </a>
                
                <?php if ($_SESSION['role'] === 'admin'): ?>
                    <a href="/views/admin.php?page=category" class="mobile-user-link">
                        <i class="fas fa-shield-alt"></i>
                        <span>Quản trị</span>
                    </a>
                <?php endif; ?>
                <a href="?action=logout" class="mobile-user-link mobile-logout-link">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Đăng xuất</span>
                </a>
            </div>
        <?php else: ?>
            <!-- Mobile Auth Section -->
            <div class="mobile-auth-section">
                <div class="mobile-auth-buttons">
                    <button class="btn mobile-btn-login" data-bs-toggle="modal" data-bs-target="#loginModal">
                        <i class="fas fa-sign-in-alt me-2"></i>
                        Đăng nhập
                    </button>
                    <button class="btn mobile-btn-register" data-bs-toggle="modal" data-bs-target="#registerModal">
                        <i class="fas fa-user-plus me-2"></i>
                        Đăng ký
                    </button>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>