<?php
// --- category.php ---

// 1. Lấy ID danh mục từ URL
$category_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
// Bạn có thể lấy tên danh mục từ API sau, hoặc nếu cần SEO tốt hơn, có thể truy vấn trước ở đây.
// Để đơn giản, ta sẽ lấy tên từ API trong JS.
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh mục - AdNews Vietnam</title> <!-- Tiêu đề tạm thời, sẽ được cập nhật sau -->
    <link rel="stylesheet" href="../public/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> <!-- jQuery cần được load trước script -->
    <style>
        /* Thêm CSS cơ bản cho trạng thái loading và lỗi */
        .loading, .error-message {
            text-align: center;
            padding: 20px;
        }
        .article-placeholder {
            height: 200px; /* Chiều cao placeholder */
            background-color: #f0f0f0;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #999;
        }
        .line-clamp-2, .line-clamp-4 {
             /* Đảm bảo CSS cho line-clamp có hiệu lực */
            display: -webkit-box;
            -webkit-line-clamp: 2; /* hoặc 4 */
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
    </style>
</head>
<body>
    <?php
    include '../views/header.php';
    ?>
    <div id="category-page">
        <div class="container-custom mt-4">
            <div class="row">
                <!-- Main Content Area for Articles -->
                <div class="col-md-8 pl-0 pr-0">
                    <h1 id="category-title">Đang tải danh mục...</h1> <!-- Tiêu đề sẽ được cập nhật -->
                    <div id="category-articles">
                         <!-- Placeholder loading -->
                        <div class="loading">
                            <div class="spinner-border" role="status">
                                <span class="visually-hidden">Loading articles...</span>
                            </div>
                        </div>
                    </div>
                     <!-- Phân trang có thể được thêm ở đây -->
                </div>

                <!-- Sidebar -->
                <?php
                include '../views/sidebar.php';
                ?>
            </div>
        </div>
    </div>

    <?php
    include '../views/footer.php';
    ?>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>

    <script>
        // URL của controller
        const API_URL = '/views/admin/controller/articles.php';
        // ID danh mục từ PHP
        const categoryId = <?php echo json_encode($category_id); ?>;
        let categoryName = '';

        // Hàm format ngày tháng (giữ nguyên từ index.php)
        function formatDate(dateString) {
            const date = new Date(dateString);
            const options = {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            };
            return date.toLocaleDateString('vi-VN', options);
        }

        // Hàm tạo HTML cho article card (dạng danh sách)
        function createArticleListItemHTML(article) {
            return `
                <div class="col-12 mb-4"> <!-- Mỗi bài viết chiếm 1 dòng -->
                    <div class="article-card d-flex"> <!-- Sử dụng flexbox để xếp hình ảnh và nội dung ngang hàng -->
                        <a href="#">
                            <img src="/public/images/articles/${article.image_url}" alt="${article.title}" class="article-image" style="width: 200px; height: auto; object-fit: cover; margin-right: 15px;">
                        </a>
                        <div class="article-content">
                            <a href="#"><h3 class="article-title line-clamp-2">${article.title}</h3></a>
                            <p class="article-excerpt line-clamp-2">${article.excerpt}</p>
                            <div class="article-meta d-flex justify-content-between align-items-center flex-wrap">
                                <div class="author-info d-flex align-items-center">
                                    <img src="/public/images/authors/${article.author_avatar}" alt="Author" class="author-avatar me-2" style="width: 30px; height: 30px; border-radius: 50%;">
                                    <div>
                                        <div style="font-weight: 500;">${article.author_name}</div>
                                        <div>${formatDate(article.publish_date)}</div>
                                    </div>
                                </div>
                                <div class="engagement-stats">
                                    <span class="me-3"><i class="fas fa-thumbs-up"></i> 4</span>
                                    <span><i class="fas fa-comment"></i> 0</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }


        // Hàm tải tên danh mục
        function loadCategoryName() {
            if (categoryId <= 0) {
                 $('#category-title').text('Danh mục không hợp lệ');
                 $('#category-articles').html('<div class="error-message">ID danh mục không hợp lệ.</div>');
                 return;
            }

            $.ajax({
                url: API_URL,
                method: 'POST',
                data: {
                    action: 'getCategories' // Lấy tất cả danh mục
                },
                dataType: 'json',
                success: function(categories) {
                    if (categories && Array.isArray(categories)) {
                        const category = categories.find(cat => parseInt(cat.id) === categoryId);
                        if (category) {
                            categoryName = category.name;
                            $('#category-title').text(categoryName);
                            document.title = categoryName + ' - AdNews Vietnam'; // Cập nhật tiêu đề trang
                             loadArticles(); // Sau khi có tên, tải bài viết
                        } else {
                            $('#category-title').text('Danh mục không tồn tại');
                            $('#category-articles').html('<div class="error-message">Không tìm thấy danh mục với ID đã cho.</div>');
                        }
                    } else {
                         $('#category-title').text('Lỗi tải danh mục');
                         $('#category-articles').html('<div class="error-message">Dữ liệu danh mục không hợp lệ.</div>');
                    }
                },
                error: function(xhr, status, error) {
                    console.error("Lỗi khi tải danh mục:", status, error);
                    $('#category-title').text('Lỗi');
                    $('#category-articles').html('<div class="error-message">Không thể tải thông tin danh mục.</div>');
                }
            });
        }

        // Hàm tải bài viết theo danh mục
        function loadArticles() {
            $('#category-articles').html(`
                <div class="loading">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Loading articles...</span>
                    </div>
                </div>
            `); // Hiển thị loading trước khi gửi request

            $.ajax({
                url: API_URL,
                method: 'POST',
                data: {
                    action: 'getArticlesByCategory',
                    category_id: categoryId
                    // Có thể thêm limit, offset cho phân trang sau
                },
                dataType: 'json',
                success: function(response) {
                    console.log("Dữ liệu bài viết nhận được:", response); // Debug
                    let html = '';
                    if (response && Array.isArray(response) && response.length > 0) {
                        html += '<div class="row">';
                        response.forEach(function(article) {
                            html += createArticleListItemHTML(article);
                        });
                        html += '</div>';
                         $('#category-articles').html(html);
                    } else {
                         $('#category-articles').html('<div class="alert alert-info">Không có bài viết nào trong danh mục này.</div>');
                    }
                },
                error: function(xhr, status, error) {
                    console.error("Lỗi khi tải bài viết:", status, error);
                    $('#category-articles').html('<div class="error-message alert alert-danger">Không thể tải bài viết. Vui lòng thử lại sau.</div>');
                }
            });
        }

        // Load trang khi document ready
        $(document).ready(function() {
            loadCategoryName(); // Bắt đầu bằng việc tải tên danh mục
        });

    </script>
</body>
</html>