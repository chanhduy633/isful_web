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
        
        .article-title {
            font-size: 2.5rem;
            font-weight: bold;
            color: #333;
            margin-bottom: 20px;
            line-height: 1.2;
        }
        
        .article-excerpt {
            font-size: 1.2rem;
            color: #666;
            font-style: italic;
            margin-bottom: 30px;
            padding-left: 20px;
            border-left: 4px solid #007bff;
        }
        
        .article-meta {
            display: flex;
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
            font-size: 0.9rem;
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
            margin-top: 30px;
            padding-top: 30px;
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
    </style>
</head>

<body>
    <?php
    include '../views/header.php';
    include '../views/login.php';
    ?>

    <div class="container-custom mt-4">
        <div class="row">
            <div class="col-md-8">
                <div class="article-detail">

                    <!-- Article hero image -->
                    <img src="/public/images/articles/<?php echo htmlspecialchars($article['image_url']); ?>" 
                         alt="<?php echo htmlspecialchars($article['title']); ?>" 
                         class="article-hero-image">

                    <!-- Article title -->
                    <h1 class="article-title"><?php echo htmlspecialchars($article['title']); ?></h1>

                    <!-- Article excerpt -->
                    <div class="article-excerpt">
                        <?php echo htmlspecialchars($article['excerpt']); ?>
                    </div>

                    <!-- Article meta -->
                    <div class="article-meta">
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
                        </div>
                        <div>
                            <?php if ($article['category_name']): ?>
                                <a href="category.php?id=<?php echo $article['category_id']; ?>" class="category-badge">
                                    <?php echo htmlspecialchars($article['category_name']); ?>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Article content -->
                    <div class="article-content">
                        <?php echo htmlspecialchars_decode($article['content'], ENT_QUOTES); ?>
                    </div>

                    <!-- Engagement actions -->
                    <div class="engagement-actions">
                        <a href="#" class="engagement-button" id="like-btn">
                            <i class="fas fa-thumbs-up"></i>
                            Thích (3)
                        </a>
                        <a href="#" class="engagement-button" id="comment-btn">
                            <i class="fas fa-comment"></i>
                            Bình luận (0)
                        </a>
                        <a href="#" class="engagement-button" id="bookmark-btn">
                            <i class="fas fa-bookmark"></i>
                            Lưu
                        </a>
                        <a href="article-detail.php?id=${article.id}" class="engagement-button" id="share-btn">
                            <i class="fas fa-share"></i>
                            Chia sẻ
                        </a>
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

    <script>
        $(document).ready(function() {
            // Xử lý các action engagement
            $('.engagement-button').click(function(e) {
                e.preventDefault();
                $(this).toggleClass('active');
            });

            // Xử lý chia sẻ
            $('#share-btn').click(function(e) {
                e.preventDefault();
                if (navigator.share) {
                    navigator.share({
                        title: '<?php echo addslashes($article['title']); ?>',
                        text: '<?php echo addslashes($article['excerpt']); ?>',
                        url: window.location.href
                    });
                } else {
                    // Fallback - copy to clipboard
                    navigator.clipboard.writeText(window.location.href).then(function() {
                        alert('Link đã được sao chép vào clipboard!');
                    });
                }
            });
        });
    </script>
</body>
</html>