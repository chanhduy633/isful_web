<?php
// Include file xử lý authentication
require 'auth_processing.php';
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

</head>

<body>
    <?php
    include '../views/header.php';
    include '../views/login.php';
    ?>

    <div id="home-page">
        <div class="container-custom mw-993 mt-4">
            <div class="row">
                <div class="col-md-8 pl-0 pr-0">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2 class="section-title">Top Pick</h2>
                    </div>

                    <!-- Main Content Section -->
                    <div id="main-content" class="row">
                        <div class="loading">
                            <div class="spinner-border" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <?php include '../views/sidebar.php'; ?>
            </div>
        </div>
    </div>

    <?php include '../views/footer.php'; ?>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="../public/js/auth.js"></script>
    <script src="../public/js/navbar.js"></script>
    <script src="../public/js/sidebar.js"></script>
    <script src="../public/js/header.js"></script>
    <script src="../public/js/interactive-helpers.js"></script>
    <script src="../public/js/interactive-system.js"></script>

    <script>
        // Load trang khi document ready
        $(document).ready(function() {
            loadMainArticles();
        });

        // Hàm format ngày tháng
        function formatDate(dateString) {
            const date = new Date(dateString);
            const options = {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            };
            return date.toLocaleDateString('vi-VN', options);
        }

        // Hàm tạo HTML cho mỗi bài viết
        function createArticleHTML(article) {
            return `
                <div class="col-12 col-md-12 mt-24">
                    <div class="article-card">
                        <div class="author-info mb-16">
                            <img src="/public/images/authors/${article.author_avatar}" alt="Author" class="author-avatar">
                            <div>
                                <div style="font-weight: 500;">${article.author_name}</div>
                                <div>${formatDate(article.publish_date)}</div>
                            </div>
                        </div>
                        <a href="article-detail.php?id=${article.id}"><img src="/public/images/articles/${article.image_url}" alt="${article.title}" class="article-image"></a>
                        <div class="article-content">
                            <a href="article-detail.php?id=${article.id}"><h3 class="article-title">${article.title}</h3></a>
                            <p class="article-excerpt">${article.excerpt}</p>
                            ${generateEngagementStats(article, { theme: 'dark' })}
                        </div>
                    </div>
                </div>
            `;
        }

        // Load bài viết
        function loadMainArticles() {
            $.ajax({
                url: '/views/admin/controller/articles.php',
                method: 'POST',
                data: {
                    action: 'getMainArticles',
                    limit: 5
                },
                dataType: 'json',
                success: function(articles) {
                    if (articles && articles.length > 0) {
                        let html = '';
                        // Tạo HTML cho mỗi bài viết
                        articles.forEach(article => {
                            html += createArticleHTML(article);
                        });
                        $('#main-content').html(html);
                    }
                },
                error: function() {
                    $('#main-content').html('<div class="alert alert-danger">Không thể tải bài viết</div>');
                }
            });
        }
    </script>
</body>

</html>