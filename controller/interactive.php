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

// Hàm kiểm tra user đã like bài viết chưa
function hasUserLiked($conn, $article_id, $user_id = null, $google_user_id = null)
{
    if ($user_id) {
        // User đã đăng nhập bằng tài khoản thường
        $stmt = $conn->prepare("SELECT id FROM article_likes WHERE article_id = ? AND user_id = ?");
        $stmt->bind_param("ii", $article_id, $user_id);
    } elseif ($google_user_id) {
        // User đã đăng nhập bằng Google
        $stmt = $conn->prepare("SELECT id FROM article_likes WHERE article_id = ? AND google_user_id = ?");
        $stmt->bind_param("ii", $article_id, $google_user_id);
    } else {
        // Không đăng nhập - không cho phép like
        return false;
    }

    $stmt->execute();
    $result = $stmt->get_result();
    return $result->num_rows > 0;
}

// Hàm thêm like
function addLike($conn, $article_id, $user_id = null, $google_user_id = null)
{
    // Kiểm tra đăng nhập
    if (!$user_id && !$google_user_id) {
        return ['success' => false, 'message' => 'Vui lòng đăng nhập để like bài viết', 'require_login' => true];
    }

    // Kiểm tra đã like chưa
    if (hasUserLiked($conn, $article_id, $user_id, $google_user_id)) {
        return ['success' => false, 'message' => 'Bạn đã like bài viết này rồi'];
    }

    // Bắt đầu transaction
    $conn->autocommit(false);

    try {
        // Thêm record vào bảng article_likes
        if ($user_id) {
            $stmt = $conn->prepare("INSERT INTO article_likes (article_id, user_id) VALUES (?, ?)");
            $stmt->bind_param("ii", $article_id, $user_id);
        } else {
            $stmt = $conn->prepare("INSERT INTO article_likes (article_id, google_user_id) VALUES (?, ?)");
            $stmt->bind_param("ii", $article_id, $google_user_id);
        }

        $stmt->execute();

        // Cập nhật số lượng likes trong bảng articles
        $updateStmt = $conn->prepare("UPDATE articles SET likes = likes + 1 WHERE id = ?");
        $updateStmt->bind_param("i", $article_id);
        $updateStmt->execute();

        // Commit transaction
        $conn->commit();
        $conn->autocommit(true);

        // Lấy số likes mới
        $newLikes = getLikesCount($conn, $article_id);

        return [
            'success' => true,
            'message' => 'Like thành công',
            'likes_count' => $newLikes,
            'user_liked' => true
        ];
    } catch (Exception $e) {
        $conn->rollback();
        $conn->autocommit(true);
        return ['success' => false, 'message' => 'Lỗi khi thêm like: ' . $e->getMessage()];
    }
}

// Hàm bỏ like
function removeLike($conn, $article_id, $user_id = null, $google_user_id = null)
{
    // Kiểm tra đăng nhập
    if (!$user_id && !$google_user_id) {
        return ['success' => false, 'message' => 'Vui lòng đăng nhập để unlike bài viết', 'require_login' => true];
    }

    // Kiểm tra đã like chưa
    if (!hasUserLiked($conn, $article_id, $user_id, $google_user_id)) {
        return ['success' => false, 'message' => 'Bạn chưa like bài viết này'];
    }

    // Bắt đầu transaction
    $conn->autocommit(false);

    try {
        // Xóa record từ bảng article_likes
        if ($user_id) {
            $stmt = $conn->prepare("DELETE FROM article_likes WHERE article_id = ? AND user_id = ?");
            $stmt->bind_param("ii", $article_id, $user_id);
        } else {
            $stmt = $conn->prepare("DELETE FROM article_likes WHERE article_id = ? AND google_user_id = ?");
            $stmt->bind_param("ii", $article_id, $google_user_id);
        }

        $stmt->execute();

        // Cập nhật số lượng likes trong bảng articles (đảm bảo không âm)
        $updateStmt = $conn->prepare("UPDATE articles SET likes = GREATEST(0, likes - 1) WHERE id = ?");
        $updateStmt->bind_param("i", $article_id);
        $updateStmt->execute();

        // Commit transaction
        $conn->commit();
        $conn->autocommit(true);

        // Lấy số likes mới
        $newLikes = getLikesCount($conn, $article_id);

        return [
            'success' => true,
            'message' => 'Bỏ like thành công',
            'likes_count' => $newLikes,
            'user_liked' => false
        ];
    } catch (Exception $e) {
        $conn->rollback();
        $conn->autocommit(true);
        return ['success' => false, 'message' => 'Lỗi khi bỏ like: ' . $e->getMessage()];
    }
}

// Hàm lấy số lượng likes của bài viết
function getLikesCount($conn, $article_id)
{
    $stmt = $conn->prepare("SELECT likes FROM articles WHERE id = ?");
    $stmt->bind_param("i", $article_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row ? intval($row['likes']) : 0;
}

// Hàm lấy trạng thái like của user cho bài viết
function getLikeStatus($conn, $article_id, $user_id = null, $google_user_id = null)
{
    $likes_count = getLikesCount($conn, $article_id);
    $user_liked = hasUserLiked($conn, $article_id, $user_id, $google_user_id);

    return [
        'likes_count' => $likes_count,
        'user_liked' => $user_liked,
        'is_logged_in' => ($user_id !== null || $google_user_id !== null)
    ];
}

// Hàm toggle like (like nếu chưa like, unlike nếu đã like)
function toggleLike($conn, $article_id, $user_id = null, $google_user_id = null)
{
    // Kiểm tra đăng nhập
    if (!$user_id && !$google_user_id) {
        return ['success' => false, 'message' => 'Vui lòng đăng nhập để like bài viết', 'require_login' => true];
    }

    if (hasUserLiked($conn, $article_id, $user_id, $google_user_id)) {
        return removeLike($conn, $article_id, $user_id, $google_user_id);
    } else {
        return addLike($conn, $article_id, $user_id, $google_user_id);
    }
}

// Hàm lấy danh sách bài viết được like nhiều nhất
function getMostLikedArticles($conn, $limit = 10)
{
    $sql = "SELECT articles.*, article_categories.name AS category_name 
            FROM articles 
            LEFT JOIN article_categories ON articles.category_id = article_categories.id 
            WHERE articles.likes > 0
            ORDER BY articles.likes DESC, articles.publish_date DESC
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

// Hàm lấy danh sách bài viết mà user đã like
function getUserLikedArticles($conn, $user_id = null, $google_user_id = null, $limit = 20, $offset = 0)
{
    if ($user_id) {
        $sql = "SELECT articles.*, article_categories.name AS category_name, article_likes.created_at as liked_at
                FROM article_likes 
                JOIN articles ON article_likes.article_id = articles.id
                LEFT JOIN article_categories ON articles.category_id = article_categories.id
                WHERE article_likes.user_id = ?
                ORDER BY article_likes.created_at DESC
                LIMIT ? OFFSET ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iii", $user_id, $limit, $offset);
    } elseif ($google_user_id) {
        $sql = "SELECT articles.*, article_categories.name AS category_name, article_likes.created_at as liked_at
                FROM article_likes 
                JOIN articles ON article_likes.article_id = articles.id
                LEFT JOIN article_categories ON articles.category_id = article_categories.id
                WHERE article_likes.google_user_id = ?
                ORDER BY article_likes.created_at DESC
                LIMIT ? OFFSET ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iii", $google_user_id, $limit, $offset);
    } else {
        return [];
    }

    $stmt->execute();
    $result = $stmt->get_result();

    $articles = [];
    while ($row = $result->fetch_assoc()) {
        $articles[] = $row;
    }

    return $articles;
}

// Lấy thông tin user từ session
function getUserInfo()
{
    session_start();

    $user_id = null;
    $google_user_id = null;
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? '';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';

    // Kiểm tra loại đăng nhập
    if (isset($_SESSION['login_type'])) {
        if ($_SESSION['login_type'] === 'normal' && isset($_SESSION['userid'])) {
            $user_id = intval($_SESSION['userid']);
        } elseif ($_SESSION['login_type'] === 'google' && isset($_SESSION['userid'])) {
            $google_user_id = intval($_SESSION['userid']);
        }
    }

    return [
        'user_id' => $user_id,
        'google_user_id' => $google_user_id,
        'ip_address' => $ip_address,
        'user_agent' => $user_agent,
        'is_logged_in' => ($user_id !== null || $google_user_id !== null)
    ];
}

// Hàm kiểm tra đăng nhập
function requireLogin()
{
    $userInfo = getUserInfo();
    if (!$userInfo['is_logged_in']) {
        return [
            'success' => false,
            'message' => 'Vui lòng đăng nhập để sử dụng tính năng này',
            'require_login' => true
        ];
    }
    return null;
}
// ===================== SAVE / UNSAVE =====================

// Hàm kiểm tra user đã save chưa
function hasUserSaved($conn, $article_id, $user_id = null, $google_user_id = null)
{
    if ($user_id) {
        $stmt = $conn->prepare("SELECT id FROM saved_articles WHERE article_id = ? AND user_id = ?");
        $stmt->bind_param("ii", $article_id, $user_id);
    } elseif ($google_user_id) {
        $stmt = $conn->prepare("SELECT id FROM saved_articles WHERE article_id = ? AND google_user_id = ?");
        $stmt->bind_param("ii", $article_id, $google_user_id);
    } else {
        return false;
    }

    $stmt->execute();
    $result = $stmt->get_result();
    return $result->num_rows > 0;
}

// Hàm toggle save (lưu / bỏ lưu)
function toggleSave($conn, $article_id, $user_id = null, $google_user_id = null)
{
    if (!$user_id && !$google_user_id) {
        return ['success' => false, 'message' => 'Vui lòng đăng nhập để lưu bài viết', 'require_login' => true];
    }

    if (hasUserSaved($conn, $article_id, $user_id, $google_user_id)) {
        // Nếu đã lưu thì bỏ lưu
        if ($user_id) {
            $stmt = $conn->prepare("DELETE FROM saved_articles WHERE article_id = ? AND user_id = ?");
            $stmt->bind_param("ii", $article_id, $user_id);
        } else {
            $stmt = $conn->prepare("DELETE FROM saved_articles WHERE article_id = ? AND google_user_id = ?");
            $stmt->bind_param("ii", $article_id, $google_user_id);
        }
        $stmt->execute();
        return ['success' => true, 'saved' => false, 'message' => 'Bài viết đã được bỏ lưu'];
    } else {
        // Nếu chưa lưu thì thêm mới
        if ($user_id) {
            $stmt = $conn->prepare("INSERT INTO saved_articles (article_id, user_id) VALUES (?, ?)");
            $stmt->bind_param("ii", $article_id, $user_id);
        } else {
            $stmt = $conn->prepare("INSERT INTO saved_articles (article_id, google_user_id) VALUES (?, ?)");
            $stmt->bind_param("ii", $article_id, $google_user_id);
        }
        $stmt->execute();
        return ['success' => true, 'saved' => true, 'message' => 'Bài viết đã được lưu'];
    }
}

// Hàm lấy trạng thái lưu
function getSaveStatus($conn, $article_id, $user_id = null, $google_user_id = null)
{
    return [
        'saved' => hasUserSaved($conn, $article_id, $user_id, $google_user_id),
        'is_logged_in' => ($user_id !== null || $google_user_id !== null)
    ];
}
// Hàm lấy trạng thái cho nhiều bài viết
function getBulkStatus($conn, $article_ids, $user_id = null, $google_user_id = null)
{
    if (empty($article_ids)) {
        return [];
    }

    $placeholders = str_repeat('?,', count($article_ids) - 1) . '?';
    
    // Lấy likes count cho tất cả bài viết
    $sql = "SELECT id, likes FROM articles WHERE id IN ($placeholders)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param(str_repeat('i', count($article_ids)), ...$article_ids);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $articles_data = [];
    while ($row = $result->fetch_assoc()) {
        $articles_data[$row['id']] = [
            'article_id' => intval($row['id']),
            'likes_count' => intval($row['likes']),
            'user_liked' => false,
            'user_saved' => false
        ];
    }

    // Nếu user đã đăng nhập, lấy trạng thái like và save
    if ($user_id || $google_user_id) {
        // Lấy trạng thái like
        if ($user_id) {
            $sql = "SELECT article_id FROM article_likes WHERE article_id IN ($placeholders) AND user_id = ?";
            $params = array_merge($article_ids, [$user_id]);
            $types = str_repeat('i', count($article_ids)) . 'i';
        } else {
            $sql = "SELECT article_id FROM article_likes WHERE article_id IN ($placeholders) AND google_user_id = ?";
            $params = array_merge($article_ids, [$google_user_id]);
            $types = str_repeat('i', count($article_ids)) . 'i';
        }
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            if (isset($articles_data[$row['article_id']])) {
                $articles_data[$row['article_id']]['user_liked'] = true;
            }
        }

        // Lấy trạng thái save
        if ($user_id) {
            $sql = "SELECT article_id FROM saved_articles WHERE article_id IN ($placeholders) AND user_id = ?";
            $params = array_merge($article_ids, [$user_id]);
            $types = str_repeat('i', count($article_ids)) . 'i';
        } else {
            $sql = "SELECT article_id FROM saved_articles WHERE article_id IN ($placeholders) AND google_user_id = ?";
            $params = array_merge($article_ids, [$google_user_id]);
            $types = str_repeat('i', count($article_ids)) . 'i';
        }
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            if (isset($articles_data[$row['article_id']])) {
                $articles_data[$row['article_id']]['user_saved'] = true;
            }
        }
    }

    return array_values($articles_data);
}


// Xử lý yêu cầu AJAX
$action = isset($_POST['action']) ? $_POST['action'] : '';

switch ($action) {
    case 'like':
        $article_id = isset($_POST['article_id']) ? intval($_POST['article_id']) : 0;

        if ($article_id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID bài viết không hợp lệ']);
            break;
        }

        $userInfo = getUserInfo();

        // Kiểm tra đăng nhập
        if (!$userInfo['is_logged_in']) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập để like bài viết', 'require_login' => true]);
            break;
        }

        $result = addLike($conn, $article_id, $userInfo['user_id'], $userInfo['google_user_id']);
        echo json_encode($result);
        break;

    case 'unlike':
        $article_id = isset($_POST['article_id']) ? intval($_POST['article_id']) : 0;

        if ($article_id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID bài viết không hợp lệ']);
            break;
        }

        $userInfo = getUserInfo();

        // Kiểm tra đăng nhập
        if (!$userInfo['is_logged_in']) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập để unlike bài viết', 'require_login' => true]);
            break;
        }

        $result = removeLike($conn, $article_id, $userInfo['user_id'], $userInfo['google_user_id']);
        echo json_encode($result);
        break;

    case 'toggle':
        $article_id = isset($_POST['article_id']) ? intval($_POST['article_id']) : 0;

        if ($article_id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID bài viết không hợp lệ']);
            break;
        }

        $userInfo = getUserInfo();

        // Kiểm tra đăng nhập
        if (!$userInfo['is_logged_in']) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập để like bài viết', 'require_login' => true]);
            break;
        }

        $result = toggleLike($conn, $article_id, $userInfo['user_id'], $userInfo['google_user_id']);
        echo json_encode($result);
        break;

    case 'getLikeStatus':
        $article_id = isset($_POST['article_id']) ? intval($_POST['article_id']) : 0;

        if ($article_id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID bài viết không hợp lệ']);
            break;
        }

        $userInfo = getUserInfo();
        $status = getLikeStatus($conn, $article_id, $userInfo['user_id'], $userInfo['google_user_id']);
        echo json_encode(['success' => true, 'data' => $status]);
        break;

    case 'getMostLiked':
        $limit = isset($_POST['limit']) ? intval($_POST['limit']) : 10;
        $articles = getMostLikedArticles($conn, $limit);
        echo json_encode(['success' => true, 'data' => $articles]);
        break;

    case 'getUserLiked':
        $limit = isset($_POST['limit']) ? intval($_POST['limit']) : 20;
        $offset = isset($_POST['offset']) ? intval($_POST['offset']) : 0;

        $userInfo = getUserInfo();

        if (!$userInfo['user_id'] && !$userInfo['google_user_id']) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập để xem danh sách yêu thích']);
            break;
        }

        $articles = getUserLikedArticles($conn, $userInfo['user_id'], $userInfo['google_user_id'], $limit, $offset);
        echo json_encode(['success' => true, 'data' => $articles]);
        break;
    case 'toggleSave':
        $article_id = isset($_POST['article_id']) ? intval($_POST['article_id']) : 0;

        if ($article_id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID bài viết không hợp lệ']);
            break;
        }

        $userInfo = getUserInfo();

        if (!$userInfo['is_logged_in']) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập để lưu bài viết', 'require_login' => true]);
            break;
        }

        $result = toggleSave($conn, $article_id, $userInfo['user_id'], $userInfo['google_user_id']);
        echo json_encode($result);
        break;

    case 'getSaveStatus':
        $article_id = isset($_POST['article_id']) ? intval($_POST['article_id']) : 0;

        if ($article_id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID bài viết không hợp lệ']);
            break;
        }

        $userInfo = getUserInfo();
        $status = getSaveStatus($conn, $article_id, $userInfo['user_id'], $userInfo['google_user_id']);
        echo json_encode(['success' => true, 'data' => $status]);
        break;
         case 'getBulkStatus':
        $article_ids_input = isset($_POST['article_ids']) ? $_POST['article_ids'] : '';
        
        if (empty($article_ids_input)) {
            echo json_encode(['success' => false, 'message' => 'Danh sách ID bài viết trống']);
            break;
        }

        $article_ids = array_map('intval', explode(',', $article_ids_input));
        $article_ids = array_filter($article_ids, function($id) {
            return $id > 0;
        });

        if (empty($article_ids)) {
            echo json_encode(['success' => false, 'message' => 'Không có ID bài viết hợp lệ']);
            break;
        }

        $userInfo = getUserInfo();
        $statuses = getBulkStatus($conn, $article_ids, $userInfo['user_id'], $userInfo['google_user_id']);
        echo json_encode(['success' => true, 'data' => $statuses]);
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Action không hợp lệ']);
        break;
}

// Đóng kết nối
$conn->close();
