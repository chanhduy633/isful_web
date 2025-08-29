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
    <link rel="stylesheet" href="../public/css/style.css">
    <link rel="stylesheet" href="../public/css/auth.css">
    <link rel="stylesheet" href="../public/css/interactive.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">

    <link rel="icon" type="image/png" href="/public/images/logo.png">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- jQuery cần được load trước khi dùng $ -->
    <!-- Google Identity Services -->
    <script src="https://accounts.google.com/gsi/client" async defer></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<body>
    <?php
    include '../views/header.php';
    include '../views/login.php';

    ?>
    <div id="home-page">
        <div class="container-custom  mt-4">
            <div class="row">
                <div class="col-md-8 pl-0 pr-0">
                    <!-- Main Content Section -->
                    <div id="main-content" class="row">
                        <div class="loading">
                            <div class="spinner-border" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                    </div>

                    <!-- Category Sections -->
                    <div id="category-sections">
                        <div class="loading">
                            <div class="spinner-border" role="status">
                                <span class="visually-hidden">Loading categories...</span>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Sidebar -->
                <?php
                include '../views/sidebar.php';
                ?>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="../public/js/auth.js"></script>
    <script src="../public/js/navbar.js"></script>
    <script src="../public/js/sidebar.js"></script>
    <script src="../public/js/search.js"></script>
    <script src="../public/js/interactive-system.js"></script>
    <script src="../public/js/interactive-helpers.js"></script>
    <?php
    include '../views/footer.php';
    ?>

    <script>
        // Load trang khi document ready
        $(document).ready(function() {
            loadMainArticles();
            loadCategorySections();
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

        // Hàm tạo HTML cho main article
        function createMainArticleHTML(article) {

            return `
                <div class="col-12 col-md-8 mt-24">
            <div class="main-article">
                <img class="w-100" src="/public/images/articles/${escapeHtml(article.image_url)}" alt="${escapeHtml(article.title)}">
                <a href="article-detail.php?id=${article.id}" class="overlay-bg"></a>
                <div class="thumbnail-describe">
                    <a href="article-detail.php?id=${article.id}">
                        <h2 class="line-clamp-2">${escapeHtml(article.title)}</h2>
                    </a> 
                    <h4 class="line-clamp-2">${escapeHtml(article.excerpt)}</h4>
                    <div class="article-meta">
                        <div class="author-info">
                            <img src="/public/images/authors/${escapeHtml(article.author_avatar)}" alt="Author" class="author-avatar">
                            <div>
                                <div style="color: white; font-weight: 500;">${escapeHtml(article.author_name)}</div>
                                <div style="color: #ccc;">${formatDate(article.publish_date)}</div>
                            </div>
                        </div>
                        ${generateEngagementStats(article, { theme: 'dark' })}
                    </div>
                </div>
            </div>
        </div>
            `;
        }

        // Hàm tạo HTML cho article card
        function createArticleCardHTML(article, options = {}) {
            const {
                showExcerpt = true,
                    showAuthor = true,
                    showEngagement = true,
                    colClass = 'col-6 col-md-4'
            } = options;

            return `
            <div class="${colClass} mt-24">
                <div class="article-card">
                    <a href="article-detail.php?id=${article.id}">
                        <img src="/public/images/articles/${escapeHtml(article.image_url)}" alt="Article" class="article-image">
                    </a>
                    <div class="article-content">
                        <a href="article-detail.php?id=${article.id}">
                            <h3 class="article-title line-clamp-2">${escapeHtml(article.title)}</h3>
                        </a>
                        ${showExcerpt ? `<p class="article-excerpt line-clamp-2">${escapeHtml(article.excerpt)}</p>` : ''}
                        ${showAuthor ? `
                            <div class="article-meta">
                                <div class="author-info">
                                    <img src="/public/images/authors/${escapeHtml(article.author_avatar)}" alt="Author" class="author-avatar">
                                    <div>
                                        <div style="font-weight: 500;">${escapeHtml(article.author_name)}</div>
                                        <div style="font-size: 10px;">${formatDate(article.publish_date)}</div>
                                    </div>
                                </div>
                                ${showEngagement ? generateEngagementStats(article) : ''}
                            </div>
                        ` : ''}
                        ${!showAuthor && showEngagement ? generateEngagementStats(article) : ''}
                    </div>
                </div>
            </div>
        `;
        }


        // Hàm tạo HTML cho small article
        function createSmallArticleHTML(article, options = {}) {
            const {
                showEngagement = true
            } = options;

            return `
        <div class="small-article">
            <a href="article-detail.php?id=${article.id}">
                <img src="/public/images/articles/${escapeHtml(article.image_url)}" alt="Article" class="small-article-image">
            </a>
            <div>
                <a class="small-article-content" href="article-detail.php?id=${article.id}">
                    <h4 class="small-article-title line-clamp-4">${escapeHtml(article.title)}</h4>
                </a>
                ${showEngagement ? generateEngagementStats(article, { size: 'small' }) : ''}
            </div>
        </div>
    `;
        }

        // Load main articles (5 bài viết mới nhất)
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

                        // Main article (bài đầu tiên)
                        html += createMainArticleHTML(articles[0]);

                        // 4 bài còn lại
                        for (let i = 1; i < articles.length && i < 5; i++) {
                            html += createArticleCardHTML(articles[i]);
                        }

                        $('#main-content').html(html);
                    }
                },
                error: function() {
                    $('#main-content').html('<div class="alert alert-danger">Không thể tải bài viết</div>');
                }
            });
        }

        // Load categories và articles theo category
        function loadCategorySections() {
            $.ajax({
                url: '/views/admin/controller/articles.php',
                method: 'POST',
                data: {
                    action: 'getCategories'
                },
                dataType: 'json',
                success: function(categories) {
                    if (categories && categories.length > 0) {
                        let sectionsHTML = '';
                        let loadedSections = 0;

                        categories.forEach(function(category) {
                            // Load articles cho mỗi category
                            $.ajax({
                                url: '/views/admin/controller/articles.php',
                                method: 'POST',
                                data: {
                                    action: 'getArticlesByCategory',
                                    category_id: category.id,
                                    limit: 4
                                },
                                dataType: 'json',
                                success: function(articles) {
                                    if (articles && articles.length > 0) {
                                        let sectionHTML = `
                                            <div class="category-section mb-5">
                                                <div class="row">
                                                    <div class="col-12">
                                                        <div class="section-header d-flex justify-content-between align-items-center mb-4">
                                                            <h2 class="section-title">${escapeHtml(category.name)}</h2>
                                                            <a href="category.php?id=${category.id}" class="see-all" style="font-weight: 700;">
                                                                Tất cả <i class="fas fa-arrow-right"></i>
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                        `;

                                        if (articles[0]) {
                                            sectionHTML += createArticleCardHTML(articles[0], {
                                                colClass: 'col-12 col-md-6',
                                                showActions: true
                                            });
                                        }

                                        // Các bài còn lại dạng small article (chiếm 1/2 chiều rộng)
                                        sectionHTML += `<div class="col-12 col-md-6">`;
                                        for (let i = 1; i < articles.length && i < 4; i++) {
                                            sectionHTML += createSmallArticleHTML(articles[i], {
                                                showEngagement: false
                                            });
                                        }
                                        sectionHTML += `</div>`; // Đóng col cho small articles

                                        sectionHTML += `
                                                </div>
                                            </div>
                                        `;

                                        sectionsHTML += sectionHTML;
                                    }

                                    loadedSections++;
                                    if (loadedSections === categories.length) {
                                        $('#category-sections').html(sectionsHTML);

                                        // Khởi tạo hệ thống tương tác sau khi tải xong
                                        if (typeof IndexInteractions !== 'undefined') {
                                            window.indexInteractions = new IndexInteractions();
                                        }
                                    }
                                },
                                error: function() {
                                    console.error('Lỗi khi tải bài viết cho danh mục:', category.name);
                                    loadedSections++;
                                }
                            });
                        });
                    }
                },
                error: function() {
                    $('#category-sections').html('<div class="alert alert-danger">Không thể tải danh mục</div>');
                }
            });
        }
    </script>
   
</body>

</html>