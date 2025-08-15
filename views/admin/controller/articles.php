<?php
header('Content-Type: application/json');

// Kết nối đến cơ sở dữ liệu
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "websiteblog";

$conn = new mysqli($servername, $username, $password, $dbname);

// Kiểm tra kết nối
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Hàm để lấy danh sách bài viết với tên danh mục
function getArticles($conn)
{
    $sql = "SELECT articles.*, article_categories.name AS category_name FROM articles LEFT JOIN article_categories ON articles.category_id = article_categories.id ORDER BY publish_date DESC";
    $result = $conn->query($sql);
    $articles = [];
    while ($row = $result->fetch_assoc()) {
        $articles[] = $row;
    }
    return $articles;
}

// Hàm để lấy 5 bài viết mới nhất cho phần main
function getMainArticles($conn, $limit = 5)
{
    $sql = "SELECT articles.*, article_categories.name AS category_name 
            FROM articles 
            LEFT JOIN article_categories ON articles.category_id = article_categories.id 
            ORDER BY publish_date DESC 
            LIMIT ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    $articles = [];
    while ($row = $result->fetch_assoc()) {
        $articles[] = $row;
    }
    return $articles;
}

// Hàm để lấy bài viết theo category_id
function getArticlesByCategory($conn, $category_id, $limit = 4)
{
    $sql = "SELECT articles.*, article_categories.name AS category_name 
            FROM articles 
            LEFT JOIN article_categories ON articles.category_id = article_categories.id 
            WHERE articles.category_id = ? 
            ORDER BY publish_date DESC 
            LIMIT ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $category_id, $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    $articles = [];
    while ($row = $result->fetch_assoc()) {
        $articles[] = $row;
    }
    return $articles;
}


// Lấy chi tiết bài viết
function getDetail($conn, $id)
{
    $stmt = $conn->prepare("SELECT * FROM articles WHERE id=?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}
// Hàm để lấy chi tiết danh mục theo ID
function getDetailCategory($conn, $id)
{
    $stmt = $conn->prepare("SELECT * FROM article_categories WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}
// Hàm để thêm bài viết
function createArticle($conn, $title, $excerpt, $content, $image_url, $author_name, $author_avatar, $publish_date, $category_id)
{
    $stmt = $conn->prepare("INSERT INTO articles (title, excerpt, content, image_url, author_name, author_avatar, publish_date, category_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssssi", $title, $excerpt, $content, $image_url, $author_name, $author_avatar, $publish_date, $category_id);
    $stmt->execute();
    return $stmt->affected_rows;
}

// Hàm để xóa bài viết theo ID
function deleteArticle($conn, $id)
{
    $stmt = $conn->prepare("DELETE FROM articles WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    return $stmt->affected_rows;
}

// Hàm để sửa bài viết theo ID
function updateArticle($conn, $id, $title, $excerpt, $content, $image_url, $author_name, $author_avatar, $publish_date, $category_id)
{
    $stmt = $conn->prepare("UPDATE articles SET title = ?, excerpt = ?, content = ?, image_url = ?, author_name = ?, author_avatar = ?, publish_date = ?, category_id = ? WHERE id = ?");
    $stmt->bind_param("sssssssii", $title, $excerpt, $content, $image_url, $author_name, $author_avatar, $publish_date, $category_id, $id);
    $stmt->execute();
    return $stmt->affected_rows;
}

// Hàm để upload ảnh
function uploadImage($file, $targetDir)
{
    if (isset($file) && $file['error'] == 0) {
        $timestamp = time();
        $imageFileType = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $validExtensions = ['jpg', 'jpeg', 'png', 'gif', 'avif', 'webp'];

        if (!in_array($imageFileType, $validExtensions)) {
            return '';
        }

        $newFileName = pathinfo($file['name'], PATHINFO_FILENAME) . '_' . $timestamp . '.' . $imageFileType;
        $targetFile = $targetDir . $newFileName;

        if (move_uploaded_file($file['tmp_name'], $targetFile)) {
            return $newFileName;
        }
    }
    return '';
}


// Hàm để lấy danh sách danh mục
function getCategories($conn)
{
    $sql = "SELECT * FROM article_categories ORDER BY name ASC";
    $result = $conn->query($sql);
    $categories = [];
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row;
    }
    return $categories;
}

// Hàm để thêm danh mục
function createCategory($conn, $name, $category_img = '', $category_description = '')
{
    $stmt = $conn->prepare("INSERT INTO article_categories (name, category_img, category_description) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $name, $category_img, $category_description);
    $stmt->execute();
    return $stmt->affected_rows;
}

// Hàm để sửa danh mục theo ID
function updateCategory($conn, $id, $name, $category_img = null, $category_description = null)
{
    if ($category_img !== null && $category_description !== null) {
        // Cập nhật cả ảnh và mô tả
        $stmt = $conn->prepare("UPDATE article_categories SET name = ?, category_img = ?, category_description = ? WHERE id = ?");
        $stmt->bind_param("sssi", $name, $category_img, $category_description, $id);
    } else if ($category_img !== null) {
        // Chỉ cập nhật ảnh
        $stmt = $conn->prepare("UPDATE article_categories SET name = ?, category_img = ? WHERE id = ?");
        $stmt->bind_param("ssi", $name, $category_img, $id);
    } else if ($category_description !== null) {
        // Chỉ cập nhật mô tả
        $stmt = $conn->prepare("UPDATE article_categories SET name = ?, category_description = ? WHERE id = ?");
        $stmt->bind_param("ssi", $name, $category_description, $id);
    } else {
        // Chỉ cập nhật tên
        $stmt = $conn->prepare("UPDATE article_categories SET name = ? WHERE id = ?");
        $stmt->bind_param("si", $name, $id);
    }
    $stmt->execute();
    return $stmt->affected_rows;
}

// Hàm để xóa danh mục theo ID
function deleteCategory($conn, $id)
{
    $stmt = $conn->prepare("DELETE FROM article_categories WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    return $stmt->affected_rows;
}

// Hàm lấy bài viết theo danh mục với phân trang
function getArticlesByCategoryWithPagination($conn, $category_id, $limit, $offset)
{
    $sql = "SELECT articles.*, article_categories.name AS category_name 
            FROM articles 
            LEFT JOIN article_categories ON articles.category_id = article_categories.id 
            WHERE articles.category_id = ? 
            ORDER BY publish_date DESC 
            LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $category_id, $limit, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
    $articles = [];
    while ($row = $result->fetch_assoc()) {
        $articles[] = $row;
    }

    // Lấy tổng số bài viết
    $countSql = "SELECT COUNT(*) as total FROM articles WHERE category_id = ?";
    $countStmt = $conn->prepare($countSql);
    $countStmt->bind_param("i", $category_id);
    $countStmt->execute();
    $countResult = $countStmt->get_result();
    $total = $countResult->fetch_assoc()['total'];

    return [
        'articles' => $articles,
        'total' => $total
    ];
}

function searchArticles($conn, $keyword)
{
    if (empty($keyword)) {
        return [];
    }

    // Chuẩn hóa từ khóa: chuyển về chữ thường để tìm kiếm không phân biệt chữ hoa
    $searchTerm = '%' . strtolower(trim($keyword)) . '%';

    $sql = "SELECT 
                articles.*, 
                article_categories.name AS category_name 
            FROM articles 
            LEFT JOIN article_categories ON articles.category_id = article_categories.id 
            WHERE 
                LOWER(articles.title) LIKE ? OR
                LOWER(articles.excerpt) LIKE ? OR
                LOWER(articles.content) LIKE ? OR
                LOWER(article_categories.name) LIKE ?
            ORDER BY 
                publish_date DESC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $searchTerm, $searchTerm, $searchTerm, $searchTerm);
    $stmt->execute();
    $result = $stmt->get_result();

    $articles = [];
    while ($row = $result->fetch_assoc()) {
        $articles[] = $row;
    }

    return $articles;
}

// Hàm để cập nhật trạng thái nổi bật
function toggleFeatured($conn, $id, $is_featured)
{
    $stmt = $conn->prepare("UPDATE articles SET is_featured = ? WHERE id = ?");
    $stmt->bind_param("ii", $is_featured, $id);
    $stmt->execute();
    return $stmt->affected_rows;
}
function getFeaturedArticles($conn, $limit = 5)
{
    $sql = "SELECT articles.*, article_categories.name AS category_name 
            FROM articles 
            LEFT JOIN article_categories ON articles.category_id = article_categories.id 
            WHERE articles.is_featured = 1 
            ORDER BY publish_date DESC 
            LIMIT ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $result = $stmt->get_result();

    $articles = [];
    while ($row = $result->fetch_assoc()) {
        $articles[] = $row;
    }
    return $articles;
}
// Xử lý yêu cầu AJAX
$action = isset($_POST['action']) ? $_POST['action'] : '';

switch ($action) {
    case 'get':
        echo json_encode(getArticles($conn));
        break;
    case 'getMainArticles':
        $limit = isset($_POST['limit']) ? intval($_POST['limit']) : 5;
        echo json_encode(getMainArticles($conn, $limit));
        break;

    case 'getArticlesByCategory':
        $category_id = isset($_POST['category_id']) ? intval($_POST['category_id']) : 0;
        $limit = isset($_POST['limit']) ? intval($_POST['limit']) : 4;
        echo json_encode(getArticlesByCategory($conn, $category_id, $limit));
        break;
    case 'getArticlesByCategoryWithPagination':
        $category_id = isset($_POST['category_id']) ? intval($_POST['category_id']) : 0;
        $limit = isset($_POST['limit']) ? intval($_POST['limit']) : 15;
        $offset = isset($_POST['offset']) ? intval($_POST['offset']) : 0;

        if ($category_id <= 0) {
            echo json_encode(['articles' => [], 'total' => 0]);
            break;
        }

        $result = getArticlesByCategoryWithPagination($conn, $category_id, $limit, $offset);
        echo json_encode($result);
        break;
    case 'getFeaturedArticles':
        $limit = isset($_POST['limit']) ? intval($_POST['limit']) : 5;
        echo json_encode(getFeaturedArticles($conn, $limit));
        break;

    case 'create':
        $title = $_POST['title'] ?? '';
        $excerpt = $_POST['excerpt'] ?? '';
        $content = $_POST['content'] ?? '';
        // $image_url = $_POST['image_url'] ?? '';
        $author_name = $_POST['author_name'] ?? '';
        // $author_avatar = $_POST['author_avatar'] ?? '';
        $publish_date = $_POST['publish_date'] ?? date('Y-m-d');
        $category_id = $_POST['category_id'] ?? 0;

        if (empty($title) || empty($content) || empty($category_id)) {
            echo json_encode(['success' => false, 'message' => 'Invalid input data']);
            break;
        }
        // Upload hình ảnh
        $articleImgDir = '../../../public/images/articles/';
        $authorImgDir = '../../../public/images/authors/';

        $image_url = uploadImage($_FILES['image_url'] ?? null, $articleImgDir);

        $author_avatar = uploadImage($_FILES['author_avatar'] ?? null, $authorImgDir);
        $result = createArticle($conn, $title, $excerpt, $content, $image_url, $author_name, $author_avatar, $publish_date, $category_id);
        echo json_encode(['success' => $result > 0]);
        break;

    case 'getDetail':
        $id = $_POST['id'] ?? 0;
        echo json_encode(getDetail($conn, $id));
        break;

    case 'update':
        $id = $_POST['id'] ?? 0;
        $title = $_POST['title'] ?? '';
        $excerpt = $_POST['excerpt'] ?? '';
        $content = $_POST['content'] ?? '';
        // $image_url = $_POST['image_url'] ?? '';
        $author_name = $_POST['author_name'] ?? '';
        // $author_avatar = $_POST['author_avatar'] ?? '';
        $publish_date = $_POST['publish_date'] ?? date('Y-m-d');
        $category_id = intval($_POST['category_id'] ?? 0); // ép sang số

        if (empty($id) || empty($title) || empty($content) || $category_id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid input data']);
            break;
        }
        // Upload hình ảnh (nếu có)
        $articleImgDir = '../../../public/images/articles/';
        $authorImgDir = '../../../public/images/authors/';

        $image_url = uploadImage($_FILES['image_url'] ?? null, $articleImgDir);
        $author_avatar = uploadImage($_FILES['author_avatar'] ?? null, $authorImgDir);

        // Lấy thông tin hiện tại nếu không upload file mới
        $current = getDetail($conn, $id);
        if (!$image_url) {
            $image_url = $current['image_url'];
        }
        if (!$author_avatar) {
            $author_avatar = $current['author_avatar'];
        }
        $result = updateArticle($conn, $id, $title, $excerpt, $content, $image_url, $author_name, $author_avatar, $publish_date, $category_id);
        echo json_encode(['success' => $result > 0]);
        break;

    case 'delete':
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        $result = deleteArticle($conn, $id);
        echo json_encode(['success' => $result > 0]);
        break;

    case 'getCategories':
        echo json_encode(getCategories($conn));
        break;

    case 'createCategory':
        $name = $_POST['name'] ?? '';
        $description = $_POST['description'] ?? '';
        $categoryImgDir = '../../../public/images/categories/';
        $category_img = uploadImage($_FILES['category_img'] ?? null, $categoryImgDir);
        echo json_encode(['success' => createCategory($conn, $name, $category_img, $description) > 0]);
        break;

    case 'updateCategory':
        $id = $_POST['id'] ?? 0;
        $name = $_POST['name'] ?? '';
        $description = $_POST['description'] ?? '';
        $categoryImgDir = '../../../public/images/categories/';
        $category_img = uploadImage($_FILES['category_img'] ?? null, $categoryImgDir);
        // Lấy thông tin hiện tại nếu không upload file mới
        $current = getDetailCategory($conn, $id);
        if (!$category_img) {
            $category_img = $current['category_img'];
        }
        $result = updateCategory($conn, $id, $name, $category_img, $description);
        echo json_encode(['success' => $result > 0]);

        break;

    case 'deleteCategory':
        $id = $_POST['id'] ?? 0;
        echo json_encode(['success' => deleteCategory($conn, $id) > 0]);
        break;

    case 'search':
        $keyword = $_POST['keyword'] ?? '';
        $results = searchArticles($conn, $keyword);
        echo json_encode($results);
        break;
    case 'toggleFeatured':
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        $is_featured = isset($_POST['is_featured']) ? intval($_POST['is_featured']) : 0;

        // Debug: Log thông tin nhận được
        error_log("Toggle Featured - ID: $id, is_featured: $is_featured");

        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID không hợp lệ: ' . $id]);
            break;
        }

        // Đảm bảo is_featured chỉ có giá trị 0 hoặc 1
        $is_featured = $is_featured ? 1 : 0;

        // Kiểm tra xem bài viết có tồn tại không
        $checkStmt = $conn->prepare("SELECT id FROM articles WHERE id = ?");
        $checkStmt->bind_param("i", $id);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();

        if ($checkResult->num_rows == 0) {
            echo json_encode(['success' => false, 'message' => 'Bài viết không tồn tại với ID: ' . $id]);
            break;
        }

        // Thực hiện cập nhật
        $updateStmt = $conn->prepare("UPDATE articles SET is_featured = ? WHERE id = ?");
        if (!$updateStmt) {
            echo json_encode(['success' => false, 'message' => 'Lỗi prepare statement: ' . $conn->error]);
            break;
        }

        $updateStmt->bind_param("ii", $is_featured, $id);
        $executeResult = $updateStmt->execute();

        if (!$executeResult) {
            echo json_encode(['success' => false, 'message' => 'Lỗi execute: ' . $updateStmt->error]);
            break;
        }

        $affected_rows = $updateStmt->affected_rows;

        // Debug: Log kết quả
        error_log("Toggle Featured Result - Affected rows: $affected_rows");

        if ($affected_rows > 0) {
            echo json_encode([
                'success' => true,
                'message' => 'Cập nhật trạng thái nổi bật thành công',
                'is_featured' => $is_featured,
                'affected_rows' => $affected_rows
            ]);
        } else {
            // Kiểm tra xem giá trị đã giống rồi không
            $currentStmt = $conn->prepare("SELECT is_featured FROM articles WHERE id = ?");
            $currentStmt->bind_param("i", $id);
            $currentStmt->execute();
            $currentResult = $currentStmt->get_result();
            $currentData = $currentResult->fetch_assoc();

            if ($currentData && $currentData['is_featured'] == $is_featured) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Trạng thái đã được cập nhật trước đó',
                    'is_featured' => $is_featured,
                    'note' => 'Giá trị không thay đổi'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Không thể cập nhật trạng thái nổi bật. Affected rows: ' . $affected_rows,
                    'current_value' => $currentData ? $currentData['is_featured'] : 'unknown',
                    'requested_value' => $is_featured
                ]);
            }
        }

        $updateStmt->close();
        break;
    default:
        echo json_encode(['error' => 'Invalid action']);
        break;
}

// Đóng kết nối
$conn->close();
