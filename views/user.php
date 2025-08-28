<?php
session_start();
require '../connect/db.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['userid']) || !isset($_SESSION['login_type'])) {
    header('Location: /views/login.php');
    exit();
}

// Lấy thông tin user
$user_id = null;
$google_user_id = null;
$username = $_SESSION['username'] ?? '';
$email = $_SESSION['email'] ?? '';
$picture = $_SESSION['picture'] ?? '';
$login_type = $_SESSION['login_type'];

if ($login_type === 'normal') {
    $user_id = intval($_SESSION['userid']);
} elseif ($login_type === 'google') {
    $google_user_id = intval($_SESSION['userid']);
}

// Hàm lấy bài viết đã like
function getUserLikedArticles($pdo, $user_id = null, $google_user_id = null)
{
    if ($user_id) {
        $sql = "SELECT articles.*, article_categories.name AS category_name, article_likes.created_at as liked_at
                FROM article_likes 
                JOIN articles ON article_likes.article_id = articles.id
                LEFT JOIN article_categories ON articles.category_id = article_categories.id
                WHERE article_likes.user_id = ?
                ORDER BY article_likes.created_at DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$user_id]);
    } elseif ($google_user_id) {
        $sql = "SELECT articles.*, article_categories.name AS category_name, article_likes.created_at as liked_at
                FROM article_likes 
                JOIN articles ON article_likes.article_id = articles.id
                LEFT JOIN article_categories ON articles.category_id = article_categories.id
                WHERE article_likes.google_user_id = ?
                ORDER BY article_likes.created_at DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$google_user_id]);
    } else {
        return [];
    }

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Hàm lấy bài viết đã lưu (giả định có bảng saved_articles)
function getUserSavedArticles($pdo, $user_id = null, $google_user_id = null)
{
    if ($user_id) {
        $sql = "SELECT articles.*, article_categories.name AS category_name, saved_articles.created_at as saved_at
                FROM saved_articles 
                JOIN articles ON saved_articles.article_id = articles.id
                LEFT JOIN article_categories ON articles.category_id = article_categories.id
                WHERE saved_articles.user_id = ?
                ORDER BY saved_articles.created_at DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$user_id]);
    } elseif ($google_user_id) {
        $sql = "SELECT articles.*, article_categories.name AS category_name, saved_articles.created_at as saved_at
                FROM saved_articles 
                JOIN articles ON saved_articles.article_id = articles.id
                LEFT JOIN article_categories ON articles.category_id = article_categories.id
                WHERE saved_articles.google_user_id = ?
                ORDER BY saved_articles.created_at DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$google_user_id]);
    } else {
        return [];
    }

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Lấy dữ liệu
$likedArticles = getUserLikedArticles($pdo, $user_id, $google_user_id);
$savedArticles = getUserSavedArticles($pdo, $user_id, $google_user_id);

// Xác định tab hiện tại
$currentTab = isset($_GET['tab']) ? $_GET['tab'] : 'liked';
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trang cá nhân - <?php echo htmlspecialchars($username); ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f7fa;
            color: #333;
        }

        .container {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            width: 280px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 0;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }

        .user-profile {
            padding: 30px 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }

        .user-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            margin: 0 auto 15px;
            border: 3px solid rgba(255, 255, 255, 0.3);
            overflow: hidden;
        }

        .user-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .user-avatar-default {
            background: rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 30px;
        }

        .user-name {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .user-email {
            font-size: 14px;
            opacity: 0.8;
        }

        .menu-list {
            list-style: none;
            padding: 20px 0;
        }

        .menu-item {
            margin-bottom: 5px;
        }

        .menu-link {
            display: flex;
            align-items: center;
            padding: 15px 25px;
            color: white;
            text-decoration: none;
            transition: all 0.3s ease;
            position: relative;
        }

        .menu-link:hover {
            background: rgba(255, 255, 255, 0.1);
            padding-left: 35px;
        }

        .menu-link.active {
            background: rgba(255, 255, 255, 0.2);
            border-right: 4px solid white;
        }

        .menu-link i {
            margin-right: 12px;
            width: 20px;
            text-align: center;
        }

        .menu-link .badge {
            background: rgba(255, 255, 255, 0.3);
            color: white;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 12px;
            margin-left: auto;
        }

        /* Main content */
        .main-content {
            flex: 1;
            margin-left: 280px;
            padding: 30px;
        }

        .page-header {
            background: white;
            padding: 25px 30px;
            border-radius: 12px;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.08);
            margin-bottom: 30px;
        }

        .page-title {
            font-size: 28px;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 8px;
        }

        .page-subtitle {
            color: #7f8c8d;
            font-size: 16px;
        }

        /* Articles grid */
        .articles-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 25px;
        }

        .article-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
        }

        .article-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15);
        }

        .article-image {
            width: 100%;
            height: 200px;
            background: linear-gradient(45deg, #f0f2f5, #e9ecef);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #6c757d;
            font-size: 24px;
        }

        .article-content {
            padding: 20px;
        }

        .article-category {
            background: #e74c3c;
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 12px;
            display: inline-block;
        }

        .article-title {
            font-size: 18px;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 10px;
            line-height: 1.4;
        }

        .article-title a {
            color: inherit;
            text-decoration: none;
        }

        .article-title a:hover {
            color: #3498db;
        }

        .article-excerpt {
            color: #7f8c8d;
            font-size: 14px;
            line-height: 1.6;
            margin-bottom: 15px;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .article-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 13px;
            color: #95a5a6;
        }

        .article-date {
            display: flex;
            align-items: center;
        }

        .article-date i {
            margin-right: 5px;
        }

        .article-stats {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .stat-item {
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #7f8c8d;
        }

        .empty-state i {
            font-size: 64px;
            margin-bottom: 20px;
            opacity: 0.5;
        }

        .empty-state h3 {
            font-size: 24px;
            margin-bottom: 10px;
            color: #34495e;
        }

        .empty-state p {
            font-size: 16px;
            line-height: 1.6;
        }

        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            transition: background 0.3s ease;
            margin-top: 15px;
        }

        .btn:hover {
            background: #2980b9;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                position: relative;
                height: auto;
            }

            .main-content {
                margin-left: 0;
                padding: 20px;
            }

            .articles-grid {
                grid-template-columns: 1fr;
            }

            .container {
                flex-direction: column;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <!-- User Profile Section -->
            <div class="user-profile">
                <div class="user-avatar">
                    <?php if ($picture): ?>
                        <img src="<?php echo htmlspecialchars($picture); ?>" alt="Avatar">
                    <?php else: ?>
                        <div class="user-avatar-default">
                            <i class="fas fa-user"></i>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="user-name"><?php echo htmlspecialchars($username); ?></div>
                <?php if ($email): ?>
                    <div class="user-email"><?php echo htmlspecialchars($email); ?></div>
                <?php endif; ?>
            </div>

            <!-- Menu Navigation -->
            <nav>
                <ul class="menu-list">
                    <li class="menu-item">
                        <a href="?tab=liked" class="menu-link <?php echo $currentTab === 'liked' ? 'active' : ''; ?>">
                            <i class="fas fa-heart"></i>
                            <span>Bài viết đã thích</span>
                            <span class="badge"><?php echo count($likedArticles); ?></span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="?tab=saved" class="menu-link <?php echo $currentTab === 'saved' ? 'active' : ''; ?>">
                            <i class="fas fa-bookmark"></i>
                            <span>Bài viết đã lưu</span>
                            <span class="badge"><?php echo count($savedArticles); ?></span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="/views/index.php" class="menu-link">
                            <i class="fas fa-home"></i>
                            <span>Quay lại trang chủ</span>
                        </a>
                    </li>
                </ul>
            </nav>


        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <?php if ($currentTab === 'liked'): ?>
                <!-- Liked Articles -->
                <div class="page-header">
                    <h1 class="page-title">
                        <i class="fas fa-heart" style="color: #e74c3c; margin-right: 10px;"></i>
                        Bài viết đã thích
                    </h1>
                    <p class="page-subtitle">Danh sách các bài viết bạn đã thích (<?php echo count($likedArticles); ?> bài viết)</p>
                </div>

                <?php if (!empty($likedArticles)): ?>
                    <div class="articles-grid">
                        <?php foreach ($likedArticles as $article): ?>
                            <article class="article-card">
                                <div class="article-image">
                                    <?php if (!empty($article['image_url'])): ?>
                                        <img src="<?php echo htmlspecialchars('/public/images/articles/' . $article['image_url']); ?>" alt="<?php echo htmlspecialchars($article['title']); ?>" style="width: 100%; height: 100%; object-fit: cover;">
                                    <?php else: ?>
                                        <i class="fas fa-image"></i>
                                    <?php endif; ?>
                                </div>
                                <div class="article-content">
                                    <?php if (!empty($article['category_name'])): ?>
                                        <span class="article-category"><?php echo htmlspecialchars($article['category_name']); ?></span>
                                    <?php endif; ?>

                                    <h3 class="article-title">
                                        <a href="/views/article-detail.php?id=<?php echo $article['id']; ?>">
                                            <?php echo htmlspecialchars($article['title']); ?>
                                        </a>
                                    </h3>

                                    <?php if (!empty($article['content'])): ?>
                                        <p class="article-excerpt">
                                            <?php echo htmlspecialchars(substr(strip_tags($article['content']), 0, 150)) . '...'; ?>
                                        </p>
                                    <?php endif; ?>

                                    <div class="article-meta">
                                        <span class="article-date">
                                            Đã thích: <?php echo date('d/m/Y', strtotime($article['liked_at'])); ?>
                                        </span>
                                        <div class="article-stats">
                                            <span class="stat-item">
                                                <i class="fas fa-heart"></i>
                                                <?php echo $article['likes'] ?? 0; ?>
                                            </span>

                                        </div>
                                    </div>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-heart-broken"></i>
                        <h3>Chưa có bài viết nào được thích</h3>
                        <p>Bạn chưa thích bài viết nào. Hãy khám phá và thích những bài viết hay nhé!</p>
                        <a href="/views/index.php" class="btn">
                            <i class="fas fa-search"></i> Khám phá bài viết
                        </a>
                    </div>
                <?php endif; ?>

            <?php elseif ($currentTab === 'saved'): ?>
                <!-- Saved Articles -->
                <div class="page-header">
                    <h1 class="page-title">
                        <i class="fas fa-bookmark" style="color: #f39c12; margin-right: 10px;"></i>
                        Bài viết đã lưu
                    </h1>
                    <p class="page-subtitle">Danh sách các bài viết bạn đã lưu để đọc sau (<?php echo count($savedArticles); ?> bài viết)</p>
                </div>

                <?php if (!empty($savedArticles)): ?>
                    <div class="articles-grid">
                        <?php foreach ($savedArticles as $article): ?>
                            <article class="article-card">
                                <div class="article-image">
                                    <?php if (!empty($article['image_url'])): ?>
                                        <img src="<?php echo htmlspecialchars('/public/images/articles/' . $article['image_url']); ?>" alt="<?php echo htmlspecialchars($article['title']); ?>" style="width: 100%; height: 100%; object-fit: cover;">
                                    <?php else: ?>
                                        <i class="fas fa-image"></i>
                                    <?php endif; ?>
                                </div>
                                <div class="article-content">
                                    <?php if (!empty($article['category_name'])): ?>
                                        <span class="article-category" style="background: #f39c12;"><?php echo htmlspecialchars($article['category_name']); ?></span>
                                    <?php endif; ?>

                                    <h3 class="article-title">
                                        <a href="/views/article.php?id=<?php echo $article['id']; ?>">
                                            <?php echo htmlspecialchars($article['title']); ?>
                                        </a>
                                    </h3>

                                    <?php if (!empty($article['content'])): ?>
                                        <p class="article-excerpt">
                                            <?php echo htmlspecialchars(substr(strip_tags($article['content']), 0, 150)) . '...'; ?>
                                        </p>
                                    <?php endif; ?>

                                    <div class="article-meta">
                                        <span class="article-date">
                                            Đã lưu: <?php echo date('d/m/Y', strtotime($article['saved_at'])); ?>
                                        </span>
                                        <div class="article-stats">
                                            <span class="stat-item">
                                                <i class="fas fa-heart"></i>
                                                <?php echo $article['likes'] ?? 0; ?>
                                            </span>

                                        </div>
                                    </div>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-bookmark"></i>
                        <h3>Chưa có bài viết nào được lưu</h3>
                        <p>Bạn chưa lưu bài viết nào. Hãy lưu những bài viết hay để đọc sau nhé!</p>
                        <a href="/views/index.php" class="btn">
                            <i class="fas fa-search"></i> Khám phá bài viết
                        </a>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </main>
    </div>

    <script>
        // Thêm hiệu ứng hover cho cards
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.article-card');

            cards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-5px)';
                });

                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                });
            });

            // Thêm animation khi load trang
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(30px)';

                setTimeout(() => {
                    card.style.transition = 'all 0.5s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            });
        });

        // Xử lý responsive menu
        function toggleMobileMenu() {
            const sidebar = document.querySelector('.sidebar');
            sidebar.classList.toggle('mobile-open');
        }
    </script>
</body>

</html>