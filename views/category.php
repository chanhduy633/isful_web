<?php
require 'auth_processing.php';
// --- category.php ---
// Lấy ID danh mục và trang hiện tại từ URL
$category_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1; // Đảm bảo trang >= 1
$limit = 15; // Số bài viết mỗi trang
$offset = ($page - 1) * $limit;
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
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://accounts.google.com/gsi/client" async defer></script>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        :root {
            --primary-color: #000;
            --secondary-color: #f8f9fa;
            --accent-color: #dc3545;
            --dark-blue: #012169;
            --mint-green: #00af50;
        }

        .category-hero {
            position: relative;
            height: 200px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 40px;
            border-radius: 15px;
            overflow: hidden;
            background-color: #000;
            background-size: cover;
            background-position: center;
            transition: background-image 0.5s ease-in-out;
        }

        .category-hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: black center/cover;
            opacity: 0.4;
            z-index: 1;
        }

        .category-hero-content {
            position: relative;
            z-index: 2;
            text-align: center;
            color: white;
        }

        .category-hero h1 {
            font-size: 3rem;
            font-weight: bold;
            margin: 0;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
        }

        .category-hero .category-description {
            font-size: 1.4rem;
            margin-top: 10px;
        }


        .engagement-stats span {
            color: #999;
            margin-right: 6px;
        }

        .engagement-stats i {
            margin-right: 5px;
        }

        .loading,
        .error-message {
            text-align: center;
            padding: 60px 20px;
        }

        .article-title {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }


        .article-excerpt {

            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .article-meta {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 0.8rem;
            color: #999;
        }

        .author-info {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        @keyframes loading {
            0% {
                background-position: 200% 0;
            }

            100% {
                background-position: -200% 0;
            }
        }

        .pagination-wrapper {
            margin-top: 50px;
            display: flex;
            justify-content: center;
        }

        .pagination .page-link {
            border: 1px solid #ccc;
            background-color: transparent;
            color: var(--mint-green);
            font-weight: 500;
            margin: 0 5px;
            border-radius: 8px;
        }

        .pagination .page-item.active .page-link {
            background-color: var(--mint-green);
            border-color: var(--mint-green);
            color: #fff;

        }

        .pagination .page-link:hover {
            background-color: var(--mint-green);
            color: #fff;
            opacity: 0.4;
        }

        @media (max-width: 768px) {
            .category-hero h1 {
                font-size: 2rem;
            }

            .category-hero {
                height: 200px;
            }
        }
    </style>
</head>

<body>
    <?php
    include '../views/header.php';
    include '../views/login.php';

    ?>

    <div id="category-page">
        <div class="container-custom mt-4">
            <div class="row">
                <!-- Main Content Area -->
                <div class="col-md-8">
                    <!-- Category Hero Section -->
                    <div class="category-hero">
                        <div class="category-hero-content">
                            <h1 id="category-title">Đang tải...</h1>
                            <div class="category-description" id="category-description">Khám phá những bài viết mới nhất</div>
                        </div>
                    </div>
                    <div id="category-articles">
                        <!-- Loading placeholders -->
                        <div class="row" id="loading-placeholders">
                            <?php for ($i = 0; $i < 3; $i++): ?>
                                <div class="col-md-4 mb-4">
                                    <div class="article-placeholder"></div>
                                </div>
                            <?php endfor; ?>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div class="pagination-wrapper" id="pagination-wrapper" style="display: none;">
                        <nav aria-label="Phân trang danh mục">
                            <ul class="pagination" id="pagination-nav"></ul>
                        </nav>
                    </div>
                </div>

                <!-- Sidebar -->
                <?php include '../views/sidebar.php'; ?>
            </div>
        </div>
    </div>

    <?php include '../views/footer.php'; ?>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="../public/js/auth.js"></script>
    <script src="../public/js/sidebar.js"></script>
    <script src="../public/js/search.js"></script>
    <script>
        const API_URL = '/views/admin/controller/articles.php';
        const categoryId = <?php echo json_encode($category_id); ?>;
        const ARTICLES_PER_PAGE = <?php echo $limit; ?>;
        let currentPage = <?php echo $page; ?>;
        let totalPages = 1;

        // Định dạng ngày
        function formatDate(dateString) {
            const date = new Date(dateString);
            const options = {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            };
            return date.toLocaleDateString('vi-VN', options);
        }

        // Tạo HTML bài viết
        function createArticleCardHTML(article) {
            return `
                <div class="col-md-4 col-sm-6 mb-4">
                    <div class="article-card">
                        <a href="article-detail.php?id=${article.id}">
                            <img src="/public/images/articles/${article.image_url}" 
                                 alt="${article.title}" class="article-image">
                        </a>
                        <div class="article-content">
                            <a href="article-detail.php?id=${article.id}" class="article-title">
                                ${article.title}
                            </a>
                            <p class="article-excerpt">${article.excerpt}</p>
                            <div class="article-meta d-flex justify-content-between align-items-center">
                                <div class="author-info d-flex align-items-center">
                                    <img src="/public/images/authors/${article.author_avatar}" 
                                         alt="Tác giả" class="author-avatar ">
                                    <div>
                                        <div style="font-weight: 600; font-size: 0.7rem;">${article.author_name}</div>
                                        <div style="font-size: 0.6rem; color: #999;">${formatDate(article.publish_date)}</div>
                                    </div>
                                </div>
                                <div class="engagement-stats">
                                    <span><i class="fas fa-thumbs-up"></i> 4</span>
                                    <span><i class="fas fa-share"></i></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }

        // Tạo phân trang
        function createPaginationHTML(currentPage, totalPages) {
            if (totalPages <= 1) return '';
            let html = '';

            if (currentPage > 1) {
                html += `<li class="page-item">
                    <a class="page-link" href="#" data-page="${currentPage - 1}">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                </li>`;
            }

            const startPage = Math.max(1, currentPage - 2);
            const endPage = Math.min(totalPages, currentPage + 2);

            if (startPage > 1) {
                html += `<li class="page-item"><a class="page-link" href="#" data-page="1">1</a></li>`;
                if (startPage > 2) {
                    html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
                }
            }

            for (let i = startPage; i <= endPage; i++) {
                html += `<li class="page-item ${i === currentPage ? 'active' : ''}">
                    <a class="page-link" href="#" data-page="${i}">${i}</a>
                </li>`;
            }

            if (endPage < totalPages) {
                if (endPage < totalPages - 1) {
                    html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
                }
                html += `<li class="page-item"><a class="page-link" href="#" data-page="${totalPages}">${totalPages}</a></li>`;
            }

            if (currentPage < totalPages) {
                html += `<li class="page-item">
                    <a class="page-link" href="#" data-page="${currentPage + 1}">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                </li>`;
            }

            return html;
        }

        // Hiển thị danh mục (có ảnh nền)
        function loadCategoryInfo() {
            if (categoryId <= 0) {
                $('#category-title').text('Danh mục không hợp lệ');
                $('#category-description').text('Danh mục không hợp lệ.');
                $('#category-articles').html('<div class="error-message alert alert-danger">ID danh mục không hợp lệ.</div>');
                $('.category-hero').css('background-image', ''); // Xóa ảnh nền
                return;
            }

            $.ajax({
                url: API_URL,
                method: 'POST',
                data: {
                    action: 'getCategories'
                },
                dataType: 'json',
                success: function(categories) {
                    const category = categories.find(cat => parseInt(cat.id) === categoryId);
                    if (category) {
                        // Cập nhật tiêu đề và mô tả
                        $('#category-title').text(category.name);
                        $('#category-description').text(category.category_description || 'Khám phá những bài viết mới nhất');
                        document.title = `${category.name} - Insightful`;

                        // Cập nhật ảnh nền cho .category-hero
                        if (category.category_img) {
                            const imgUrl = `/public/images/categories/${encodeURIComponent(category.category_img)}`;
                            $('.category-hero').css('background-image', `url('${imgUrl}')`);
                        } else {
                            // Ảnh dự phòng nếu không có
                            $('.category-hero').css('background-image', 'url(/public/images/categories/default.jpg)');
                        }

                        // Tải bài viết sau khi có danh mục
                        loadArticles(currentPage);
                    } else {
                        showError('Không tìm thấy danh mục.');
                        $('.category-hero').css('background-image', 'url(/public/images/categories/default.jpg)');
                    }
                },
                error: function() {
                    showError('Không thể tải thông tin danh mục.');
                    $('.category-hero').css('background-image', 'url(/public/images/categories/default.jpg)');
                }
            });
        }

        // Tải bài viết theo danh mục và phân trang
        function loadArticles(page = 1) {
            $('#category-articles').html(`
                <div class="row">
                    ${Array.from({length: 3}, () => ` <
                div class = "col-md-4 mb-4" >
                <
                div class = "article-placeholder" > < /div> < /
                div >
                `).join('')}
                </div>
            `);
            $('#pagination-wrapper').hide();

            $.ajax({
                url: API_URL,
                method: 'POST',
                data: {
                    action: 'getArticlesByCategoryWithPagination',
                    category_id: categoryId,
                    limit: ARTICLES_PER_PAGE,
                    offset: (page - 1) * ARTICLES_PER_PAGE
                },
                dataType: 'json',
                success: function(response) {
                    if (response && response.articles && Array.isArray(response.articles)) {
                        displayArticles(response.articles);
                        totalPages = Math.ceil(response.total / ARTICLES_PER_PAGE);
                        if (totalPages > 1) {
                            displayPagination(page, totalPages);
                        }
                        // Cập nhật URL
                        const newUrl = `${window.location.pathname}?id=${categoryId}&page=${page}`;
                        window.history.pushState({
                            page
                        }, '', newUrl);
                    } else {
                        $('#category-articles').html('<div class="alert alert-info text-center">Chưa có bài viết nào.</div>');
                    }
                },
                error: function() {
                    showError('Không thể tải bài viết.');
                }
            });
        }

        function displayArticles(articles) {
            let html = '<div class="row">';
            if (articles.length > 0) {
                articles.forEach(article => {
                    html += createArticleCardHTML(article);
                });
            } else {
                html += '<div class="col-12"><div class="alert alert-info text-center">Không có bài viết nào trong danh mục này.</div></div>';
            }
            html += '</div>';
            $('#category-articles').html(html);
        }

        function displayPagination(currentPage, totalPages) {
            $('#pagination-nav').html(createPaginationHTML(currentPage, totalPages));
            $('#pagination-wrapper').show();
        }

        function showError(message) {
            $('#category-articles').html(`<div class="error-message alert alert-danger text-center">${message}</div>`);
        }

        // Xử lý click phân trang
        $(document).on('click', '.pagination .page-link', function(e) {
            e.preventDefault();
            const page = $(this).data('page');
            if (page && page !== currentPage) {
                currentPage = page;
                loadArticles(page);
            }
        });

        // Hỗ trợ nút back/forward trình duyệt
        window.addEventListener('popstate', function(e) {
            const page = e.state?.page || 1;
            currentPage = page;
            loadArticles(page);
        });

        // Khởi tạo
        $(document).ready(function() {
            loadCategoryInfo();
        });
    </script>
</body>

</html>