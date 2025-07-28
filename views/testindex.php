<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AdNews Vietnam</title>
    <link rel="stylesheet" href="../public/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- jQuery cần được load trước khi dùng $ -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<body>
    <?php
    include '../views/header.php';
    ?>
    <div id="home-page">
        <div class="container-custom mw-993 mt-4">
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
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
                    <div class="main-article" style="background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('/public/images/articles/${article.image_url}');">
                        <h2 class="mb-3">${article.title}</h2>
                        <p class="mb-4">${article.excerpt}</p>
                        <div class="article-meta">
                            <div class="author-info">
                                <img src="/public/images/authors/${article.author_avatar}" alt="Author" class="author-avatar">
                                <div>
                                    <div style="color: white; font-weight: 500;">${article.author_name}</div>
                                    <div style="color: #ccc;">${formatDate(article.publish_date)}</div>
                                </div>
                            </div>
                            <div class="engagement-stats">
                                <span><i class="fas fa-thumbs-up"></i> 3</span>
                                <span><i class="fas fa-comment"></i> 0</span>
                                <span><i class="fas fa-bookmark"></i></span>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }

        // Hàm tạo HTML cho article card
        function createArticleCardHTML(article) {
            return `
                <div class="col-6 col-md-4 mt-24">
                    <div class="article-card">
                        <img src="/public/images/articles/${article.image_url}" alt="Article" class="article-image">
                        <div class="article-content">
                            <h3 class="article-title">${article.title}</h3>
                            <p class="article-excerpt">${article.excerpt}</p>
                            <div class="article-meta">
                                <div class="author-info">
                                    <img src="/public/images/authors/${article.author_avatar}" alt="Author" class="author-avatar">
                                    <div>
                                        <div style="font-weight: 500;">${article.author_name}</div>
                                        <div>${formatDate(article.publish_date)}</div>
                                    </div>
                                </div>
                                <div class="engagement-stats">
                                    <span><i class="fas fa-thumbs-up"></i> 4</span>
                                    <span><i class="fas fa-comment"></i> 0</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }

        // Hàm tạo HTML cho small article
        function createSmallArticleHTML(article) {
            return `
                <div class="small-article">
                    <img src="/public/images/articles/${article.image_url}" alt="Article" class="small-article-image">
                    <div class="small-article-content">
                        <h4 class="small-article-title">${article.title}</h4>
                        <div class="small-article-meta">${article.author_name} • ${formatDate(article.publish_date)}</div>
                    </div>
                </div>
            `;
        }

        // Load main articles (5 bài viết mới nhất)
        function loadMainArticles() {
            $.ajax({
                url: '/views/admin/controller/articles.php', // Thay đổi đường dẫn này
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
                url: '/views/admin/controller/articles.php', // Thay đổi đường dẫn này
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
                                url: '/views/admin/controller/articles.php', // Thay đổi đường dẫn này
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
                                            <div class="row mt-4">
                                                <div class="col-12">
                                                    <div class="d-flex justify-content-between align-items-center mb-4">
                                                        <h2 class="section-title">${category.name}</h2>
                                                        <a href="#" class="see-all">
                                                            Tất cả <i class="fas fa-arrow-right"></i>
                                                        </a>
                                                    </div>
                                                </div>
                                                <div class="col-12 col-md-6">
                                        `;

                                        // Main article của category (bài đầu tiên)
                                        if (articles[0]) {
                                            sectionHTML += `
                                                <div class="article-card">
                                                    <img src="/public/images/articles/${articles[0].image_url}" alt="Article" class="article-image">
                                                    <div class="article-content">
                                                        <h3 class="article-title">${articles[0].title}</h3>
                                                        <p class="article-excerpt">${articles[0].excerpt}</p>
                                                        <div class="article-meta">
                                                            <div class="author-info">
                                                                <img src="/public/images/authors/${articles[0].author_avatar}" alt="Author" class="author-avatar">
                                                                <div>
                                                                    <div style="font-weight: 500;">${articles[0].author_name}</div>
                                                                    <div>${formatDate(articles[0].publish_date)}</div>
                                                                </div>
                                                            </div>
                                                            <div class="engagement-stats">
                                                                <span><i class="fas fa-thumbs-up"></i> 4</span>
                                                                <span><i class="fas fa-comment"></i> 0</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            `;
                                        }

                                        sectionHTML += `
                                                </div>
                                                <div class="col-12 col-md-6">
                                        `;

                                        // 3 bài còn lại dạng small
                                        for (let i = 1; i < articles.length && i < 4; i++) {
                                            sectionHTML += createSmallArticleHTML(articles[i]);
                                        }

                                        sectionHTML += `
                                                </div>
                                            </div>
                                        `;

                                        sectionsHTML += sectionHTML;
                                    }

                                    loadedSections++;
                                    if (loadedSections === categories.length) {
                                        $('#category-sections').html(sectionsHTML);
                                    }
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