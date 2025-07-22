<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý danh mục bài viết</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
            <h2>Danh mục bài viết</h2>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createCategoryModal">Thêm danh mục</button>
        </div>

        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tên danh mục</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody id="categories">
                <tr><td colspan="3">Loading...</td></tr>
            </tbody>
        </table>

        <!-- Modal thêm danh mục -->
        <div class="modal fade" id="createCategoryModal">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Thêm danh mục mới</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="text" id="category_name" class="form-control" placeholder="Tên danh mục">
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                        <button class="btn btn-success" id="createCategory">Thêm danh mục</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal sửa danh mục -->
        <div class="modal fade" id="editCategoryModal">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Chỉnh sửa danh mục</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="edit_category_id">
                        <input type="text" id="edit_category_name" class="form-control">
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                        <button class="btn btn-primary" id="updateCategory">Lưu thay đổi</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        function loadCategories() {
            $.post('/views/admin/controller/articles.php', { action: 'getCategories' }, function(data) {
                let html = '';
                data.forEach(cat => {
                    html += `<tr>
                        <td>${cat.id}</td>
                        <td>${cat.name}</td>
                        <td>
                            <button class="btn btn-warning btn-sm" onclick="editCategory(${cat.id}, '${cat.name}')">Sửa</button>
                            <button class="btn btn-danger btn-sm" onclick="deleteCategory(${cat.id})">Xóa</button>
                        </td>
                    </tr>`;
                });
                $('#categories').html(html);
            });
        }

        $('#createCategory').click(function() {
            const name = $('#category_name').val().trim();
            if (name == '') {
                alert('Vui lòng nhập tên danh mục!');
                return;
            }
            $.post('/views/admin/controller/articles.php', {
                action: 'createCategory',
                name: name
            }, function(response) {
                if (response.success) {
                    $('#createCategoryModal').modal('hide');
                    $('#category_name').val('');
                    loadCategories();
                } else {
                    alert('Thêm thất bại');
                }
            });
        });

        function editCategory(id, name) {
            $('#edit_category_id').val(id);
            $('#edit_category_name').val(name);
            $('#editCategoryModal').modal('show');
        }

        $('#updateCategory').click(function() {
            const id = $('#edit_category_id').val();
            const name = $('#edit_category_name').val().trim();
            if (name == '') {
                alert('Vui lòng nhập tên danh mục!');
                return;
            }
            $.post('/views/admin/controller/articles.php', {
                action: 'updateCategory',
                id: id,
                name: name
            }, function(response) {
                if (response.success) {
                    $('#editCategoryModal').modal('hide');
                    loadCategories();
                } else {
                    alert('Cập nhật thất bại');
                }
            });
        });

        function deleteCategory(id) {
            if (confirm('Bạn chắc chắn muốn xóa danh mục này?')) {
                $.post('/views/admin/controller/articles.php', {
                    action: 'deleteCategory',
                    id: id
                }, function(response) {
                    if (response.success) {
                        loadCategories();
                    } else {
                        alert('Xóa thất bại');
                    }
                });
            }
        }

        $(document).ready(loadCategories);
    </script>
</body>
</html>
