<?php
session_start();
if (!isset($_SESSION["userid"]) && !isset($_SESSION["username"])) {
    header('Location: ../views/login.php');
}
$get = isset($_GET["page"]) ? $_GET["page"] : "";

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Trang Admin</title>
    <link rel="icon" type="image/png" sizes="32x32" href="../public/images/logo.png">

    <link rel="stylesheet" href="../public/css/bootstrap.min.css" type="text/css">
    <link rel="stylesheet" href="../public/css/style.css?v=1.0.0">

    <style>
        .navbar a {
            color: white;
            text-decoration: none;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <header class="navbar navbar-dark sticky-top bg-dark flex-md-nowrap p-0 shadow">
        <a class="col-md-3 col-lg-2 me-0 px-3 text-center" href="#">ADMIN</a>
        <button class="navbar-toggler position-absolute d-md-none collapsed" type="button" data-bs-toggle="collapse"
            data-bs-target="#sidebarMenu" aria-controls="sidebarMenu" aria-expanded="false"
            aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="navbar-nav">
            <div class="nav-item text-nowrap">
                <a class="nav-link px-3" href="./logout.php">Đăng xuất</a>
            </div>
        </div>
    </header>
    <div class="container-fluid">
        <div class="row">
            <nav id="sidebarMenu" class="col-md-3 vh-100 col-lg-2 d-md-block bg-light sidebar collapse">
                <div class="position-sticky pt-3">
                    <ul class="nav flex-column">


                        <li class="nav-item">
                            <a class="nav-link active" aria-current="page" href="/views/index.php">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" class="feather feather-home" aria-hidden="true">
                                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                                    <polyline points="9 22 9 12 15 12 15 22"></polyline>
                                </svg>
                                Trang chủ
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/views/admin.php?page=category">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                    style="fill: rgba(24, 85, 219, 1);transform: scaleX(-1);msFilter:progid:DXImageTransform.Microsoft.BasicImage(rotation=0, mirror=1);">
                                    <path d="M10 3H4a1 1 0 0 0-1 1v6a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4a1 1 0 0 0-1-1zM9 9H5V5h4v4zm11-6h-6a1 1 0 0 0-1 1v6a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4a1 1 0 0 0-1-1zm-1 6h-4V5h4v4zm-9 4H4a1 1 0 0 0-1 1v6a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1v-6a1 1 0 0 0-1-1zm-1 6H5v-4h4v4zm8-6c-2.206 0-4 1.794-4 4s1.794 4 4 4 4-1.794 4-4-1.794-4-4-4zm0 6c-1.103 0-2-.897-2-2s.897-2 2-2 2 .897 2 2-.897 2-2 2z"></path>
                                </svg>
                                Quản lý danh mục
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/views/admin.php?page=article">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                    style="fill: rgba(24, 85, 219, 1);transform: scaleX(-1);msFilter:progid:DXImageTransform.Microsoft.BasicImage(rotation=0, mirror=1);">
                                    <path d="M10 3H4a1 1 0 0 0-1 1v6a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4a1 1 0 0 0-1-1zM9 9H5V5h4v4zm11-6h-6a1 1 0 0 0-1 1v6a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4a1 1 0 0 0-1-1zm-1 6h-4V5h4v4zm-9 4H4a1 1 0 0 0-1 1v6a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1v-6a1 1 0 0 0-1-1zm-1 6H5v-4h4v4zm8-6c-2.206 0-4 1.794-4 4s1.794 4 4 4 4-1.794 4-4-1.794-4-4-4zm0 6c-1.103 0-2-.897-2-2s.897-2 2-2 2 .897 2 2-.897 2-2 2z"></path>
                                </svg>
                                Quản lý bài viết
                            </a>
                        </li>

                        <!-- Menu quản lý người dùng -->
                        <li class="nav-item">
                            <a class="nav-link" href="/views/admin.php?page=manage_users">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                    fill="currentColor" class="bi bi-people">
                                    <path d="M5 8c1.104 0 2-.896 2-2s-.896-2-2-2-2 .896-2 2 .896 2 2 2zm14 0c1.104 0 2-.896 2-2s-.896-2-2-2-2 .896-2 2 .896 2 2 2zm-6 2c1.104 0 2-.896 2-2s-.896-2-2-2-2 .896-2 2 .896 2 2 2zm-8 1c-1.105 0-2 .895-2 2v5h4v-5c0-1.105-.895-2-2-2zm14 0c-1.105 0-2 .895-2 2v5h4v-5c0-1.105-.895-2-2-2zm-7 1c-1.105 0-2 .895-2 2v5h4v-5c0-1.105-.895-2-2-2z" />
                                </svg>
                                Quản lý người dùng
                            </a>
                        </li>



                    </ul>
                </div>
            </nav>

            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="chartjs-size-monitor">
                    <div class="chartjs-size-monitor-expand">
                        <div class=""></div>
                    </div>
                    <div class="chartjs-size-monitor-shrink">
                        <div class=""></div>
                    </div>
                </div>

                <?php
                // if ($get == "dashboard") {
                //     include("../views/admin/news/index.php");
                // }
                if ($get == "category" && $_SESSION["role"] == 'admin') {
                    include("../views/admin/articles/category.php");
                }
                if ($get == "article" && $_SESSION["role"] == 'admin') {
                    include("../views/admin/articles/index.php");
                }
                if ($get == "add"  && $_SESSION["role"] == 'admin') {
                    include("../views/admin/articles/create.php");
                }
                // Tích hợp các trang quản lý tài khoản
                if ($get == "manage_users" && $_SESSION["role"] == 'admin') {
                    include("../views/admin/account/admin_manage_users.php");
                }
                if ($get == "add_user" && $_SESSION["role"] == 'admin') {
                    include("../views/admin/account/add_user.php");
                }
                if ($get == "edit_user" && $_SESSION["role"] == 'admin') {
                    include("../views/admin/account/edit_user.php");
                }
                if ($get == "order") {
                    include("../views/admin/order/manageorders.php");
                }
                ?>
            </main>
        </div>
    </div>
    <script src="../public/js/jquery-3.7.1.min.js"></script>
    <script src="../public/js/bootstrap.min.js"></script>

</body>

</html>