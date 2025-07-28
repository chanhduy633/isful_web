<?php
// articles_detail.php
$articleId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Chi tiết bài viết</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .article-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            font-family: Arial, sans-serif;
        }
        .article-header {
            margin-bottom: 30px;
            border-bottom: 1px solid #eee;
            padding-bottom: 15px;
        }
        .article-title {
            font-size: 2.2em;
            margin-bottom: 10px;
            color: #333;
        }
        .article-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            color: #666;
            font-size: 0.9em;
            margin-bottom: 20px;
        }
        .article-image {
            max-width: 100%;
            height: auto;
            margin: 20px 0;
            border-radius: 4px;
        }
        .article-excerpt {
            font-size: 1.1em;
            line-height: 1.6;
            color: #444;
            font-style: italic;
            margin-bottom: 30px;
            padding: 10px;
            background-color: #f9f9f9;
            border-left: 3px solid #3498db;
        }
        .article-content {
            font-size: 1.05em;
            line-height: 1.8;
            color: #333;
        }
        .loading {
            text-align: center;
            padding: 50px;
            font-size: 1.2em;
            color: #777;
        }
        .error {
            color: #e74c3c;
            padding: 20px;
            border: 1px solid #e74c3c;
            background-color: #fdeded;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="article-container">
        <div id="article-loading" class="loading">Đang tải bài viết...</div>
        <div id="article-error" class="error" style="display:none;"></div>
        
        <div id="article-content" style="display:none;">
            <div class="article-header">
                <h1 id="article-title" class="article-title"></h1>
                <div class="article-meta">
                    <span>Tác giả: <span id="article-author"></span></span>
                    <span>Ngày xuất bản: <span id="article-date"></span></span>
                    <span>Chuyên mục: <span id="article-category"></span></span>
                </div>
            </div>
            
            <div id="article-image-container">
                <img id="article-image" class="article-image" src="" alt="Ảnh bài viết">
            </div>
            
            <div id="article-excerpt" class="article-excerpt"></div>
            
            <div id="article-body" class="article-content"></div>
        </div>
    </div>

    <script>
    $(document).ready(function() {
        const articleId = <?= $articleId ?>;
        
        if (!articleId) {
            showError('Không tìm thấy bài viết');
            return;
        }

        $.ajax({
            url: '/views/admin/controller/articles.php',
            type: 'GET',
            dataType: 'json',
            data: { id: articleId },
            success: function(response) {
                if (response.error) {
                    showError(response.error);
                    return;
                }

                renderArticle(response);
            },
            error: function(xhr) {
                showError(`Lỗi tải dữ liệu: ${xhr.statusText}`);
            }
        });

        function renderArticle(article) {
            $('#article-title').text(article.title);
            $('#article-author').text(article.author_name);
            $('#article-date').text(article.publish_date);
            $('#article-category').text(article.category_name);
            $('#article-image').attr('src', article.imagePath);
            $('#article-excerpt').text(article.excerpt);
            $('#article-body').html(article.content);

            // Ẩn loading, hiển thị nội dung
            $('#article-loading').hide();
            $('#article-content').fadeIn();
        }

        function showError(message) {
            $('#article-loading').hide();
            $('#article-error').text(message).show();
        }
    });
    </script>
</body>
</html>