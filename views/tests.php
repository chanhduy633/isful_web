<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog Website</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .container-custom {
            max-width: 993px;
        }
        .mt-24 {
            margin-top: 24px;
        }
        
        /* Main Article Styles */
        .main-article {
            height: 400px;
            background-size: cover !important;
            background-position: center !important;
            border-radius: 12px;
            padding: 24px;
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            color: white;
            position: relative;
        }
        
        .main-article h2 {
            font-size: 1.75rem;
            font-weight: 700;
            margin-bottom: 16px;
        }
        
        .main-article p {
            font-size: 1rem;
            opacity: 0.9;
            margin-bottom: 24px;
        }
        
        .article-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .author-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .author-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }
        
        .engagement-stats {
            display: flex;
            gap: 16px;
            align-items: center;
        }
        
        .engagement-stats span {
            display: flex;
            align-items: center;
            gap: 4px;
            font-size: 0.875rem;
        }
        
        /* Article Card Styles */
        .article-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .article-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 24px rgba(0,0,0,0.12);
        }
        
        .article-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        
        .article-content {
            padding: 20px;
        }
        
        .article-title {
            font-size: 1.125rem;
            font-weight: 600;
            margin-bottom: 12px;
            line-height: 1.4;
            color: #1a1a1a;
        }
        
        .article-excerpt {
            font-size: 0.875rem;
            color: #666;
            margin-bottom: 16px;
            line-height: 1.5;
        }
        
        /* Small Article Styles */
        .small-article {
            display: flex;
            gap: 12px;
            padding: 16px 0;
            border-bottom: 1px solid #eee;
        }
        
        .small-article:last-child {
            border-bottom: none;
        }
        
        .small-article-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
            flex-shrink: 0;
        }
        
        .small-article-content {
            flex: 1;
        }
        
        .small-article-title {
            font-size: 0.875rem;
            font-weight: 600;
            margin-bottom: 8px;
            line-height: 1.4;
            color: #1a1a1a;
        }
        
        .small-article-meta {
            font-size: 0.75rem;
            color: #999;
        }
        
        /* Section Styles */
        .section-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0;
        }
        
        .see-all {
            text-decoration: none;
            color: #007bff;
            font-weight: 500;
            font-size: 0.875rem;
        }
        
        .see-all:hover {
            color: #0056b3;
        }
        
        /* Loading Styles */
        .loading {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 200px;
        }
        
        .spinner-border {
            color: #007bff;
        }

        /* Sidebar Placeholder */
        .sidebar {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 24px;
            height: fit-content;
        }
    </style>
</head>
<body>
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
                <div class="col-md-4">
                    <div class="sidebar">
                        <h5>Sidebar Content</h5>
                        <p>This is sidebar placeholder content.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    
    <script>
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
                url: 'path/to/your/controller.php', // Thay đổi đường dẫn này
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
                url: 'path/to/your/controller.php', // Thay đổi đường dẫn này
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
                                url: 'path/to/your/controller.php', // Thay đổi đường dẫn này
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

        // Load trang khi document ready
        $(document).ready(function() {
            loadMainArticles();
            loadCategorySections();
        });
    </script>
</body>
</html>