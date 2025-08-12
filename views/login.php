
<!-- Login Modal -->
<div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title" id="loginModalLabel">
                    <i class="fas fa-sign-in-alt me-2"></i>Đăng nhập
                </h5>
                <button type="button" class="btn-closed fa-solid fa-xmark" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="loginForm">
                    <div class="mb-3">
                        <label for="username" class="form-label">Tên đăng nhập</label>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Mật khẩu</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 mb-3">
                        <i class="fas fa-sign-in-alt me-1"></i>Đăng nhập
                    </button>
                </form>

                <!-- Divider -->
                <div class="d-flex align-items-center my-3">
                    <hr class="flex-grow-1">
                    <span class="mx-3 text-muted">HOẶC</span>
                    <hr class="flex-grow-1">
                </div>

                <!-- Google Sign-In -->
                <div class="d-flex justify-content-center">
                    <div id="g_id_onload"
                        data-client_id="<?php echo $google_client_id; ?>"
                        data-callback="handleCredentialResponse"
                        data-auto_prompt="false">
                    </div>
                    <div class="g_id_signin"
                        data-type="standard"
                        data-size="large"
                        data-theme="outline"
                        data-text="sign_in_with"
                        data-shape="rectangular"
                        data-logo_alignment="left"
                        data-width="280">
                    </div>
                </div>

                <div id="loginMessage" class="mt-3"></div>
            </div>
            <div class="modal-footer border-0">
                <p class="text-center w-100 mb-0">
                    Chưa có tài khoản?
                    <a href="#" data-bs-dismiss="modal" data-bs-toggle="modal" data-bs-target="#registerModal">Đăng ký </a>
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Register Modal -->
<div class="modal fade" id="registerModal" tabindex="-1" aria-labelledby="registerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title" id="registerModalLabel">
                    <i class="fas fa-user-plus me-2"></i>Đăng ký tài khoản
                </h5>
                <button type="button" class="btn-closed fa-solid fa-xmark" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="registerForm">
                    <div class="mb-3">
                        <label for="reg_username" class="form-label">Tên đăng nhập</label>
                        <input type="text" class="form-control" id="reg_username" name="reg_username" required>
                    </div>
                    <div class="mb-3">
                        <label for="reg_password" class="form-label">Mật khẩu</label>
                        <input type="password" class="form-control" id="reg_password" name="reg_password" required>
                        <div class="form-text">Mật khẩu phải có ít nhất 6 ký tự</div>
                    </div>
                    <div class="mb-3">
                        <label for="reg_confirm_password" class="form-label">Xác nhận mật khẩu</label>
                        <input type="password" class="form-control" id="reg_confirm_password" name="reg_confirm_password" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-user-plus me-1"></i>Đăng ký
                    </button>
                </form>

                <div id="registerMessage" class="mt-3"></div>
            </div>
            <div class="modal-footer border-0">
                <p class="text-center w-100 mb-0">
                    Đã có tài khoản?
                    <a href="#" data-bs-dismiss="modal" data-bs-toggle="modal" data-bs-target="#loginModal">Đăng nhập </a>
                </p>
            </div>
        </div>
    </div>
</div>