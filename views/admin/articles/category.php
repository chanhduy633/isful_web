<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý danh mục bài viết</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Reset các style có thể bị conflict từ Quill */
        .table th,
        .table td {
            vertical-align: middle;
        }

        .btn-group-sm>.btn,
        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
            border-radius: 0.2rem;
        }

        /* Đảm bảo modal hoạt động đúng */
        .modal {
            z-index: 1055;
        }

        /* Style cho ảnh danh mục */
        .category-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
            border: 2px solid #dee2e6;
        }

        .image-preview {
            max-width: 200px;
            max-height: 200px;
            border-radius: 8px;
            border: 2px solid #dee2e6;
            margin-top: 10px;
        }
    </style>
</head>

<body>
    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
            <h2>Danh mục bài viết</h2>
            <button class="btn btn-primary" id="btnAddCategory">Thêm danh mục</button>
        </div>

        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Tên danh mục</th>
                        <th>Ảnh danh mục</th>
                        <th>Mô tả danh mục</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody id="categories">
                    <tr>
                        <td colspan="5" class="text-center">Đang tải...</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Modal thêm danh mục -->
        <div class="modal fade" id="createCategoryModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Thêm danh mục mới</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form id="createCategoryForm" enctype="multipart/form-data">
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="category_name" class="form-label">Tên danh mục</label>
                                <input type="text" id="category_name" name="name" class="form-control" placeholder="Nhập tên danh mục">
                                <div class="invalid-feedback" id="create_error_msg"></div>
                            </div>
                            <div class="mb-3">
                                <label for="category_image" class="form-label">Ảnh danh mục</label>
                                <input type="file" id="category_image" name="category_img" class="form-control" accept="image/*">
                            </div>
                            <div class="mb-3">
                                <label for="category_description" class="form-label">Mô tả danh mục</label>
                                <textarea id="category_description" name="description" class="form-control" rows="3" placeholder="Nhập mô tả cho danh mục (tùy chọn)"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                            <button type="button" class="btn btn-success" id="createCategory">Thêm danh mục</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal sửa danh mục -->
        <div class="modal fade" id="editCategoryModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Chỉnh sửa danh mục</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form id="editCategoryForm" enctype="multipart/form-data">
                        <div class="modal-body">
                            <input type="hidden" id="edit_category_id" name="id">
                            <div class="mb-3">
                                <label for="edit_category_name" class="form-label">Tên danh mục</label>
                                <input type="text" id="edit_category_name" name="name" class="form-control">
                                <div class="invalid-feedback" id="edit_error_msg"></div>
                            </div>
                            <div class="mb-3">
                                <label for="edit_category_image" class="form-label">Ảnh danh mục</label>
                                <input type="file" id="edit_category_image" name="category_img" class="form-control" accept="image/*">
                                <div class="mt-2">
                                    <img id="edit_image_preview" class="image-preview" style="display: none;">
                                    <p class="text-muted mt-2">Để trống nếu không muốn thay đổi ảnh</p>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="edit_category_description" class="form-label">Mô tả danh mục</label>
                                <textarea id="edit_category_description" name="description" class="form-control" rows="3" placeholder="Nhập mô tả cho danh mục (tùy chọn)"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                            <button type="button" class="btn btn-primary" id="updateCategory">Lưu thay đổi</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts - Load theo thứ tự chính xác -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // Đảm bảo DOM ready trước khi thực thi
        $(document).ready(function() {
            console.log('Category page loaded successfully');

            // Load categories khi trang được tải
            loadCategories();

            // Event handlers
            setupEventHandlers();
        });

        function setupEventHandlers() {
            // Nút thêm danh mục
            $('#btnAddCategory').off('click').on('click', function() {
                $('#category_name').val('').removeClass('is-invalid');
                $('#category_image').val('');
                $('#category_description').val('');
                $('#create_error_msg').text('');
                $('#createCategoryModal').modal('show');
            });

            // Nút tạo danh mục
            $('#createCategory').off('click').on('click', function() {
                createCategory();
            });

            // Nút cập nhật danh mục
            $('#updateCategory').off('click').on('click', function() {
                updateCategory();
            });

            // Preview ảnh khi upload - Edit modal
            $('#edit_category_image').off('change').on('change', function() {
                previewImage(this, '#edit_image_preview');
            });

            // Enter key support
            $('#category_name').off('keypress').on('keypress', function(e) {
                if (e.which === 13) {
                    createCategory();
                }
            });

            $('#edit_category_name').off('keypress').on('keypress', function(e) {
                if (e.which === 13) {
                    updateCategory();
                }
            });
        }

        function previewImage(input, previewSelector) {
            const file = input.files[0];
            const preview = $(previewSelector);

            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.attr('src', e.target.result).show();
                }
                reader.readAsDataURL(file);
            } else {
                preview.hide();
            }
        }

        function loadCategories() {
            console.log('Loading categories...');

            $.ajax({
                url: '/views/admin/controller/articles.php',
                type: 'POST',
                data: {
                    action: 'getCategories'
                },
                dataType: 'json',
                success: function(data) {
                    console.log('Categories loaded:', data);

                    if (Array.isArray(data) && data.length > 0) {
                        let html = '';
                        data.forEach(cat => {
                            // Xử lý ảnh danh mục
                            let imageHtml = '';
                            if (cat.category_img && cat.category_img.trim() !== '') {
                                imageHtml = `<img src="/public/images/categories/${escapeHtml(cat.category_img)}" class="category-image" alt="Category image">`;
                            } else {
                                imageHtml = '<span class="text-muted">Không có ảnh</span>';
                            }
                            // Xử lý mô tả danh mục
                            let descriptionHtml = '';
                            if (cat.category_description && cat.category_description.trim() !== '') {
                                const shortDesc = cat.category_description.length > 50 ?
                                    cat.category_description.substring(0, 50) + '...' :
                                    cat.category_description;
                                descriptionHtml = `<span title="${escapeHtml(cat.category_description)}">${escapeHtml(shortDesc)}</span>`;
                            } else {
                                descriptionHtml = '<span class="text-muted">Không có mô tả</span>';
                            }
                            html += `<tr>
                                <td>${cat.id}</td>
                                <td>${escapeHtml(cat.name)}</td>
                                <td>${imageHtml}</td>
                                <td>${descriptionHtml}</td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <button class="btn btn-warning btn-sm" onclick="editCategory(${cat.id}, '${escapeHtml(cat.name)}', '${escapeHtml(cat.category_img || '')}')">
                                            <i class="bi bi-pencil"></i> Sửa
                                        </button>
                                        <button class="btn btn-danger btn-sm" onclick="deleteCategory(${cat.id})">
                                            <i class="bi bi-trash"></i> Xóa
                                        </button>
                                    </div>
                                </td>
                            </tr>`;
                        });
                        $('#categories').html(html);
                    } else {
                        $('#categories').html('<tr><td colspan="5" class="text-center text-muted">Chưa có danh mục nào</td></tr>');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error loading categories:', error);
                    $('#categories').html('<tr><td colspan="5" class="text-center text-danger">Lỗi khi tải danh mục</td></tr>');
                }
            });
        }

        function createCategory() {
            const formData = new FormData($('#createCategoryForm')[0]);
            formData.append('action', 'createCategory');

            const name = $('#category_name').val().trim();

            // Validation
            if (name === '') {
                $('#category_name').addClass('is-invalid');
                $('#create_error_msg').text('Vui lòng nhập tên danh mục!');
                return;
            }

            // Remove validation classes
            $('#category_name').removeClass('is-invalid');
            $('#create_error_msg').text('');

            // Disable button to prevent double submission
            $('#createCategory').prop('disabled', true).text('Đang thêm...');

            $.ajax({
                url: '/views/admin/controller/articles.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(response) {
                    if (response && response.success) {
                        $('#createCategoryModal').modal('hide');
                        $('#createCategoryForm')[0].reset();
                        loadCategories();

                        Swal.fire({
                            title: 'Thành công!',
                            text: 'Danh mục đã được thêm',
                            icon: 'success',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    } else {
                        Swal.fire({
                            title: 'Thất bại!',
                            text: response.message || 'Không thể thêm danh mục',
                            icon: 'error'
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error creating category:', error);
                    Swal.fire({
                        title: 'Lỗi!',
                        text: 'Có lỗi xảy ra khi thêm danh mục',
                        icon: 'error'
                    });
                },
                complete: function() {
                    $('#createCategory').prop('disabled', false).text('Thêm danh mục');
                }
            });
        }

        function editCategory(id, name, categoryImg, categoryDescription) {
            $('#edit_category_id').val(id);
            $('#edit_category_name').val(name).removeClass('is-invalid');
            $('#edit_category_image').val('');
            $('#edit_category_description').val(categoryDescription || '');
            $('#edit_error_msg').text('');

            // Hiển thị ảnh hiện tại nếu có
            if (categoryImg && categoryImg.trim() !== '') {
                $('#edit_image_preview').attr('src', '/public/images/categories/' + categoryImg).show();
            } else {
                $('#edit_image_preview').hide();
            }

            $('#editCategoryModal').modal('show');
        }

        function updateCategory() {
            const formData = new FormData($('#editCategoryForm')[0]);
            formData.append('action', 'updateCategory');

            const name = $('#edit_category_name').val().trim();

            // Validation
            if (name === '') {
                $('#edit_category_name').addClass('is-invalid');
                $('#edit_error_msg').text('Vui lòng nhập tên danh mục!');
                return;
            }

            // Remove validation classes
            $('#edit_category_name').removeClass('is-invalid');
            $('#edit_error_msg').text('');

            // Disable button
            $('#updateCategory').prop('disabled', true).text('Đang cập nhật...');

            $.ajax({
                url: '/views/admin/controller/articles.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(response) {
                    if (response && response.success) {
                        $('#editCategoryModal').modal('hide');
                        loadCategories();

                        Swal.fire({
                            title: 'Thành công!',
                            text: 'Danh mục đã được cập nhật',
                            icon: 'success',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    } else {
                        Swal.fire({
                            title: 'Thất bại!',
                            text: response.message || 'Không thể cập nhật danh mục',
                            icon: 'error'
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error updating category:', error);
                    Swal.fire({
                        title: 'Lỗi!',
                        text: 'Có lỗi xảy ra khi cập nhật danh mục',
                        icon: 'error'
                    });
                },
                complete: function() {
                    $('#updateCategory').prop('disabled', false).text('Lưu thay đổi');
                }
            });
        }

        function deleteCategory(id) {
            Swal.fire({
                title: 'Bạn chắc chắn muốn xóa?',
                text: 'Danh mục này sẽ bị xóa vĩnh viễn!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Xóa',
                cancelButtonText: 'Hủy'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '/views/admin/controller/articles.php',
                        type: 'POST',
                        data: {
                            action: 'deleteCategory',
                            id: id
                        },
                        dataType: 'json',
                        success: function(response) {
                            if (response && response.success) {
                                loadCategories();
                                Swal.fire({
                                    title: 'Đã xóa!',
                                    text: 'Danh mục đã được xóa',
                                    icon: 'success',
                                    timer: 2000,
                                    showConfirmButton: false
                                });
                            } else {
                                Swal.fire({
                                    title: 'Thất bại!',
                                    text: response.message || 'Không thể xóa danh mục',
                                    icon: 'error'
                                });
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('Error deleting category:', error);
                            Swal.fire({
                                title: 'Lỗi!',
                                text: 'Có lỗi xảy ra khi xóa danh mục',
                                icon: 'error'
                            });
                        }
                    });
                }
            });
        }

        function escapeHtml(unsafe) {
            if (!unsafe) return '';
            return unsafe
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }
    </script>
</body>

</html>