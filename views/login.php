<?php
require '../connect/db.php';
session_start();
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE username = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        // echo "Đăng nhập thành công!";
        $_SESSION['userid'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        header('Location: ../views/index.php');
    } else {
        // echo "<p>Tên đăng nhập hoặc mật khẩu không đúng!</p>";
        $loginError = true;
    }
}

if (isset($_SESSION["userid"]) && $_SESSION["username"]) {
    if ($_SESSION['role'] == 'admin') {
        header('Location: ../views/admin.php?page=category');
    } else {
        header('Location: /views/index.php');
    }
}


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>login</title>
    <link rel="icon" type="image/png" sizes="32x32" href="../public/images/logo.png">

    <link rel="stylesheet" href="../public/css/bootstrap.min.css" type="text/css">
    <link rel="stylesheet" href="../public/css/style.css?v=1.0.0">

</head>

<body style="background-color:#e3ecf4;">
    <section>
        <div class="container">


        </div>
        <div class="container">
            <div class="row mt-5">
                <div class="col-12 col-sm-7 col-md-6 m-auto text-center mx-auto my-3">
                    <div class="card border-0 shadow" style="min-height: 400px; background-color:#d3e0eb;">
                        <div class="card-body p-5 ">
                            <div class="mb-4">
                                <svg class="" xmlns="http://www.w3.org/2000/svg" width="60" height="60" fill="currentColor" class="bi bi-person-circle" viewBox="0 0 16 16">
                                    <path d="M11 6a3 3 0 1 1-6 0 3 3 0 0 1 6 0z" />
                                    <path fill-rule="evenodd" d="M0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8zm8-7a7 7 0 0 0-5.468 11.37C3.242 11.226 4.805 10 8 10s4.757 1.225 5.468 2.37A7 7 0 0 0 8 1z" />
                                </svg>
                            </div>
                            <h1 class="">Đăng nhập</h1>

                            <form method="post" action="">
                                <input type="text" name="username" class="form-control my-4 py-2" id="usernameid" required placeholder="Nhập tên" />
                                <input type="password" name="password" class="form-control my-4 py-2" id="passwordid" required placeholder="Nhập mật khẩu" />
                                <div class="text-center mt-3">
                                    <button class="btn btn-primary mb-3" type="submit">Đăng nhập</button>
                                    <div id="message">
                                        <?php if (isset($loginError)): ?>
                                            <div class="alert alert-danger">Tên đăng nhập hoặc mật khẩu không đúng!</div>
                                        <?php endif; ?>
                                    </div>
                                    <a href="register.php" class="btn btn-link"> Tạo tài khoản</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>
    <script src="../public/js/jquery-3.7.1.min.js"></script>
    <script src="../public/js/bootstrap.min.js"></script>


</body>

</html>