<?php
// Include file xử lý authentication
require 'auth_processing.php';

// Lấy ID bài viết từ URL
$article_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($article_id <= 0) {
    header('Location: index.php');
    exit;
}

// Kết nối database để lấy chi tiết bài viết
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "websiteblog";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Lấy thông tin chi tiết bài viết
$stmt = $conn->prepare("SELECT articles.*, article_categories.name AS category_name 
                        FROM articles 
                        LEFT JOIN article_categories ON articles.category_id = article_categories.id 
                        WHERE articles.id = ?");
$stmt->bind_param("i", $article_id);
$stmt->execute();
$result = $stmt->get_result();
$article = $result->fetch_assoc();

if (!$article) {
    header('Location: index.php');
    exit;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($article['title']); ?> - Insightful</title>
    <link rel="stylesheet" href="../public/css/style.css">
    <link rel="stylesheet" href="../public/css/auth.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" type="image/png" href="/public/images/logo.png">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://accounts.google.com/gsi/client" async defer></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <style>
        /* Sticky Author Sidebar */
        .author-sticky-sidebar {
            position: fixed;
            left: 20px;
            top: 50%;
            transform: translateY(-50%);
            width: 60px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            padding: 15px 0;
            z-index: 1000;
            text-align: center;
            transition: all 0.3s ease;
            border: 1px solid #eee;
        }

        .author-sticky-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            margin: 0 auto 8px auto;
            display: block;
            border: 2px solid #007bff;
        }

        .author-sticky-name {
            font-size: 0.7rem;
            color: #333;
            margin-bottom: 10px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 50px;
        }

        .author-sticky-actions {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .action-btn {
            color: #666;
            font-size: 0.9rem;
            transition: all 0.2s;
            display: block;
        }

        .action-btn:hover {
            color: #007bff;
            transform: scale(1.1);
        }

        .action-btn.active {
            color: #007bff;
        }

        /* Ẩn trên màn hình nhỏ */
        @media (max-width: 768px) {
            .author-sticky-sidebar {
                display: none;
            }
        }

        .article-detail {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }

        .article-hero-image {
            width: 100%;
            height: 400px;
            object-fit: cover;
            border-radius: 10px;
            margin-bottom: 30px;
        }

        .article-title-full {
            font-size: 2.5rem;
            font-weight: bold;
            color: #333;
            margin-bottom: 20px;
            line-height: 1.2;
        }

        .article-excerpt-full {
            font-size: 1.2rem;
            color: #666;
            font-style: italic;
            margin-bottom: 30px;
            padding-left: 20px;
            border-left: 4px solid #007bff;
        }

        .article-meta {
            display: block;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 30px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 10px;
        }

        .author-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .author-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
        }

        .author-details h5 {
            margin: 0;
            font-weight: 600;
            color: #333;
        }

        .publish-date {
            color: #666;
            font-size: 0.8rem;
            line-height: 14px;
        }

        .category-badge {
            background: #007bff;
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            text-decoration: none;
        }

        .article-content {
            font-size: 1.1rem;
            line-height: 1.8;
            color: #333;
            margin-bottom: 40px;
        }

        .article-content p {
            margin-bottom: 20px;
        }

        .article-content img {
            width: 100%;
            border-radius: 8px;
        }

        .back-button {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #007bff;
            text-decoration: none;
            font-weight: 500;
            margin-bottom: 30px;
        }

        .back-button:hover {
            color: #0056b3;
            text-decoration: none;
        }

        .engagement-actions {
            display: flex;
            gap: 20px;
            align-items: center;
            margin-top: 12px;
            padding-top: 12px;
            border-top: 1px solid #eee;
        }

        .engagement-button {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            border: 1px solid #ddd;
            border-radius: 25px;
            background: white;
            color: #666;
            text-decoration: none;
            transition: all 0.3s;
        }

        .engagement-button:hover {
            background: #f8f9fa;
            color: #333;
            text-decoration: none;
        }

        .engagement-button.active {
            background: #007bff;
            color: white;
            border-color: #007bff;
        }

        /* Bookmark button state */
        #bookmark-btn.bookmarked,
        #sticky-bookmark-btn.bookmarked {
            background: #007bff;
            color: white;
            border-color: #007bff;
        }

        /* Loading state */
        #bookmark-btn.loading,
        #sticky-bookmark-btn.loading {
            opacity: 0.7;
            pointer-events: none;
        }
    </style>
</head>

<body>
    <?php
    include '../views/header.php';
    include '../views/login.php';
    ?>

    <div class="container-custom mt-4">
        <div class="row">

            <!-- Sticky Author Sidebar -->
            <div class="author-sticky-sidebar">
                <div class="author-sticky-content">
                    <img src="/public/images/authors/<?php echo htmlspecialchars($article['author_avatar']); ?>"
                        alt="<?php echo htmlspecialchars($article['author_name']); ?>"
                        class="author-sticky-avatar">
                    <div class="author-sticky-name">
                        <?php echo htmlspecialchars($article['author_name']); ?>
                    </div>
                    <div class="author-sticky-actions">
                        <a href="#" class="action-btn" id="sticky-like-btn" title="Thích">
                            <i class="fas fa-heart"></i>
                        </a>

                        <a href="#" class="action-btn" id="sticky-bookmark-btn" title="Lưu bài viết">
                            <i class="fas fa-bookmark"></i>
                        </a>
                        <a href="#" class="action-btn" id="sticky-share-btn" title="Chia sẻ">
                            <i class="fas fa-share"></i>
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="article-detail">

                    <!-- Article hero image -->
                    <img src="/public/images/articles/<?php echo htmlspecialchars($article['image_url']); ?>"
                        alt="<?php echo htmlspecialchars($article['title']); ?>"
                        class="article-hero-image">

                    <!-- Article title -->
                    <h1 class="article-title-full"><?php echo htmlspecialchars($article['title']); ?></h1>

                    <!-- Article excerpt -->
                    <div class="article-excerpt-full">
                        <?php echo htmlspecialchars($article['excerpt']); ?>
                    </div>

                    <!-- Article meta -->
                    <div class="article-meta">

                        <div>
                            <div class="author-info">
                                <img src="/public/images/authors/<?php echo htmlspecialchars($article['author_avatar']); ?>"
                                    alt="<?php echo htmlspecialchars($article['author_name']); ?>"
                                    class="author-avatar">
                                <div class="author-details">
                                    <h5><?php echo htmlspecialchars($article['author_name']); ?></h5>
                                    <div class="publish-date">
                                        <?php
                                        $date = new DateTime($article['publish_date']);
                                        echo $date->format('d/m/Y');
                                        ?>
                                    </div>
                                </div>
                                <?php if ($article['category_name']): ?>
                                    <a href="category.php?id=<?php echo $article['category_id']; ?>" class="category-badge">
                                        <?php echo htmlspecialchars($article['category_name']); ?>
                                    </a>
                                <?php endif; ?>
                            </div>

                        </div>
                        <!-- Engagement actions -->
                        <div class="engagement-actions">
                            <button class="engagement-button" id="like-btn" data-article-id="<?php echo $article_id; ?>">
                                <i class="fas fa-heart"></i>
                                <span>Thích (<span id="likes-count">0</span>)</span>
                            </button>

                            <button class="engagement-button" id="bookmark-btn" data-article-id="<?php echo $article_id; ?>">
                                <i class="fas fa-bookmark"></i>
                                <span>Lưu</span>
                            </button>
                            <button class="engagement-button" id="share-btn">
                                <i class="fas fa-share"></i>
                                <span>Chia sẻ</span>
                            </button>
                        </div>
                    </div>

                    <!-- Article content -->
                    <div class="article-content">
                        <?php echo htmlspecialchars_decode($article['content'], ENT_QUOTES); ?>
                    </div>


                </div>
            </div>

            <!-- Sidebar -->
            <?php include '../views/sidebar.php'; ?>
        </div>
    </div>

    <!-- Footer -->
    <?php include '../views/footer.php'; ?>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="../public/js/auth.js"></script>
    <script src="../public/js/sidebar.js"></script>
    <script src="../public/js/search.js"></script>

    <script>
        class LikeSystem {
            constructor(articleId) {
                this.articleId = articleId;
                this.init();
            }

            init() {
                this.bindEvents();
                this.loadLikeStatus();
                this.loadBookmarkStatus();
            }

            bindEvents() {
                // Main like button
                $('#like-btn').on('click', (e) => {
                    e.preventDefault();
                    this.toggleLike();
                });

                // Sticky like button
                $('#sticky-like-btn').on('click', (e) => {
                    e.preventDefault();
                    this.toggleLike();
                });

                // Bookmark button
                $('#bookmark-btn').on('click', (e) => {
                    e.preventDefault();
                    this.toggleBookmark();
                });
                // Sticky bookmark button
                $('#sticky-bookmark-btn').on('click', (e) => {
                    e.preventDefault();
                    this.toggleBookmark();
                });

                // Share button
                $('#share-btn, #sticky-share-btn').on('click', (e) => {
                    e.preventDefault();
                    this.shareArticle();
                });
            }

            async toggleLike() {
                if ($('#like-btn').hasClass('loading')) return;

                this.setLoading(true);

                try {
                    const response = await fetch('/controller/interactive.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `action=toggle&article_id=${this.articleId}`
                    });

                    const data = await response.json();

                    if (data.success) {
                        this.updateLikeUI(data.likes_count, data.user_liked);
                        this.showToast(data.message, 'success');
                    } else {
                        if (data.require_login) {
                            // Yêu cầu đăng nhập - hiển thị prompt
                            this.showLoginPrompt();
                        } else {
                            this.showToast(data.message || 'Có lỗi xảy ra', 'error');
                        }
                    }
                } catch (error) {
                    this.showToast('Không thể kết nối đến server', 'error');
                }

                this.setLoading(false);
            }

            async loadLikeStatus() {
                try {
                    const response = await fetch('/controller/interactive.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `action=getLikeStatus&article_id=${this.articleId}`
                    });

                    const result = await response.json();

                    if (result.success) {
                        this.updateLikeUI(result.data.likes_count, result.data.user_liked);
                    }
                } catch (error) {
                    console.error('Load like status error:', error);
                }
            }

            updateLikeUI(likesCount, userLiked) {
                // Update like count
                $('#likes-count').text(likesCount);
                $('#sticky-likes-count').text(likesCount);

                // Update button states
                const likeButtons = $('#like-btn, #sticky-like-btn');

                if (userLiked) {
                    likeButtons.addClass('liked');
                    $('#like-btn').find('span').html(`Đã thích (${likesCount})`);
                } else {
                    likeButtons.removeClass('liked');
                    $('#like-btn').find('span').html(`Thích (${likesCount})`);
                }
            }

            showLoginPrompt() {

                // Hiển thị thông báo trong modal
                const messageDiv = $('#loginMessage');
                messageDiv.html('<div class="alert alert-info mb-0">Vui lòng đăng nhập để sử dụng tính năng này.</div>');

                // Mở modal đăng nhập Bootstrap
                const loginModal = new bootstrap.Modal(document.getElementById('loginModal'));
                loginModal.show();
            }


            openLoginModal() {
                // Gọi lại showLoginPrompt để đảm bảo thông báo được hiển thị
                this.showLoginPrompt();
            }

            toggleBookmark() {
                const bookmarkBtns = $('#bookmark-btn, #sticky-bookmark-btn');

                // Kiểm tra đăng nhập trước
                this.checkLoginForAction('bookmark').then((canProceed) => {
                    if (!canProceed) return;

                    const isBookmarked = bookmarkBtns.hasClass('bookmarked');

                    if (isBookmarked) {
                        bookmarkBtns.removeClass('bookmarked');
                        $('#bookmark-btn').find('span').text('Lưu');
                        this.showToast('Đã bỏ lưu bài viết', 'success');
                    } else {
                        bookmarkBtns.addClass('bookmarked');
                        $('#bookmark-btn').find('span').text('Đã lưu');
                        this.showToast('Đã lưu bài viết', 'success');
                    }
                });
            }
            async toggleBookmark() {

                if ($('#bookmark-btn').hasClass('loading')) {
                    return;
                }

                this.setBookmarkLoading(true);

                try {
                    const response = await fetch('/controller/interactive.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `action=toggleSave&article_id=${this.articleId}`
                    });


                    if (!response.ok) {
                        console.error('❌ HTTP error! Status:', response.status);
                        this.showToast('Lỗi mạng hoặc server.', 'error');
                        this.setBookmarkLoading(false);
                        return;
                    }

                    const data = await response.json();

                    if (data.success) {
                        this.updateBookmarkUI(data.saved);
                        this.showToast(data.message, 'success');
                    } else {
                        if (data.require_login) {
                            this.showLoginPrompt();
                        } else {
                            console.error('❌ Backend error:', data.message);
                            this.showToast(data.message || 'Có lỗi xảy ra', 'error');
                        }
                    }
                } catch (error) {
                    console.error('💥 Fetch error:', error);
                    this.showToast('Không thể kết nối đến server.', 'error');
                }

                this.setBookmarkLoading(false);
            }

            async loadBookmarkStatus() {

                try {
                    const response = await fetch('/controller/interactive.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `action=getSaveStatus&article_id=${this.articleId}`
                    });


                    if (!response.ok) {
                        console.error('❌ Status check failed:', response.status);
                        return;
                    }

                    const result = await response.json();

                    if (result.success) {
                        this.updateBookmarkUI(result.data.saved);
                    } else {
                        console.warn('⚠️ Failed to load bookmark status:', result.message);
                    }
                } catch (error) {
                    console.error('🚨 Error checking bookmark status:', error);
                }
            }


            updateBookmarkUI(isBookmarked) {

                const bookmarkBtns = $('#bookmark-btn, #sticky-bookmark-btn');
                const textSpan = $('#bookmark-btn').find('span');

                if (isBookmarked) {
                    bookmarkBtns.addClass('bookmarked');
                    textSpan.text('Đã lưu');
                    bookmarkBtns.find('i').removeClass('far').addClass('fas');
                } else {
                    bookmarkBtns.removeClass('bookmarked');
                    textSpan.text('Lưu');
                    bookmarkBtns.find('i').removeClass('fas').addClass('far');
                }
;
            }

            setBookmarkLoading(loading) {
                const buttons = $('#bookmark-btn, #sticky-bookmark-btn');
                if (loading) {
                    buttons.addClass('loading').prop('disabled', true);
                    buttons.find('i').removeClass('fa-bookmark').addClass('fa-spinner fa-spin');
                } else {
                    buttons.removeClass('loading').prop('disabled', false);
                    buttons.find('i').removeClass('fa-spinner fa-spin').addClass('fa-bookmark');

                    // Restore correct icon based on current state
                    if (buttons.hasClass('bookmarked')) {
                        buttons.find('i').addClass('fas').removeClass('far');
                    } else {
                        buttons.find('i').addClass('far').removeClass('fas');
                    }
                }
            }
            async checkLoginForAction(actionType) {
                try {
                    const response = await fetch('/controller/interactive.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `action=getLikeStatus&article_id=${this.articleId}`
                    });

                    const result = await response.json();

                    if (result.success && !result.data.is_logged_in) {
                        this.showToast(`Vui lòng đăng nhập để ${actionType === 'bookmark' ? 'lưu bài viết' : 'sử dụng tính năng này'}`, 'error');
                        this.showLoginPrompt();
                        return false;
                    }
                    return true;
                } catch (error) {
                    console.error('Check login error:', error);
                    return true; // Cho phép tiếp tục nếu có lỗi
                }
            }

            shareArticle() {
                const title = <?php echo json_encode($article['title']); ?>;
                const excerpt = <?php echo json_encode($article['excerpt']); ?>;
                const url = window.location.href;

                if (navigator.share) {
                    navigator.share({
                        title: title,
                        text: excerpt,
                        url: url
                    }).then(() => {
                        this.showToast('Đã chia sẻ thành công', 'success');
                    }).catch((error) => {
                        console.log('Share error:', error);
                    });
                } else {
                    // Fallback - copy to clipboard
                    navigator.clipboard.writeText(url).then(() => {
                        this.showToast('Link đã được sao chép vào clipboard!', 'success');
                    }).catch(() => {
                        // Fallback for older browsers
                        const textArea = document.createElement('textarea');
                        textArea.value = url;
                        document.body.appendChild(textArea);
                        textArea.focus();
                        textArea.select();
                        document.execCommand('copy');
                        document.body.removeChild(textArea);
                        this.showToast('Link đã được sao chép!', 'success');
                    });
                }
            }

            setLoading(loading) {
                const buttons = $('#like-btn, #sticky-like-btn');
                if (loading) {
                    buttons.addClass('loading').prop('disabled', true);
                } else {
                    buttons.removeClass('loading').prop('disabled', false);
                }
            }

            showToast(message, type = 'success') {
                const toastId = 'toast-' + Date.now();
                const toast = $(`
                    <div class="toast ${type}" id="${toastId}">
                        <div class="toast-message">${message}</div>
                    </div>
                `);

                $('#toast-container').append(toast);

                // Trigger animation
                setTimeout(() => {
                    toast.addClass('show');
                }, 100);

                // Auto remove
                setTimeout(() => {
                    toast.removeClass('show');
                    setTimeout(() => {
                        toast.remove();
                    }, 300);
                }, 3000);
            }
        }

        // Initialize when document is ready
        $(document).ready(function() {
            const articleId = <?php echo $article_id; ?>;
            window.likeSystem = new LikeSystem(articleId);
        });
    </script>
</body>
</body>

</html>