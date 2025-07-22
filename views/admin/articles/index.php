<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý bài viết</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
            <h2>Danh sách bài viết</h2>
            <button class="btn btn-primary" id="btnAdd">Thêm bài viết</button>
        </div>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tiêu đề</th>
                    <th>Trích dẫn</th>
                    <th>Nội dung</th>
                    <th>Tác giả</th>
                    <th>Hình ảnh</th>
                    <th>Ngày đăng</th>
                    <th>Danh mục</th>
                    <th>Xóa/Sửa</th>
                </tr>
            </thead>
            <tbody id="articleList">
                <tr>
                    <td colspan="9">Loading...</td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Modal thêm/sửa -->
    <div class="modal fade" id="modalCreate">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 id="modalTitle">Thêm bài viết</h5>
                    <button class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="articleForm" enctype="multipart/form-data">
                        <input type="hidden" id="articleId">
                        <input class="form-control mb-2" id="title" placeholder="Tiêu đề">
                        <textarea class="form-control mb-2" id="excerpt" placeholder="Trích dẫn"></textarea>
                        <textarea class="form-control mb-2" id="content" placeholder="Nội dung"></textarea>

                        <label>Ảnh bài viết</label>
                        <input type="file" class="form-control mb-2" id="image_url">

                        <input class="form-control mb-2" id="author_name" placeholder="Tên tác giả">

                        <label>Avatar tác giả</label>
                        <input type="file" class="form-control mb-2" id="author_avatar">

                        <input class="form-control mb-2" type="date" id="publish_date">
                        <select class="form-control mb-2" id="category_id">
                            <option value="">Chọn danh mục</option>
                        </select>
                    </form>

                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button class="btn btn-success" id="saveBtn">Lưu bài viết</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function() {
            loadCategories();
            loadArticles();

            $('#btnAdd').click(function() {
                $('#modalTitle').text('Thêm bài viết mới');
                $('#articleForm')[0].reset();
                $('#articleId').val('');
                $('#modalCreate').modal('show');
            });

            $('#saveBtn').click(function() {
                let articleId = $('#articleId').val().trim();
                let action = articleId ? 'update' : 'create';

                let formData = new FormData();
                formData.append('action', action);
                formData.append('id', articleId);
                formData.append('title', $('#title').val());
                formData.append('excerpt', $('#excerpt').val());
                formData.append('content', $('#content').val());

                // lấy file ảnh bài viết
                if ($('#image_url')[0].files[0]) {
                    formData.append('image_url', $('#image_url')[0].files[0]);
                }

                formData.append('author_name', $('#author_name').val());

                // lấy file avatar tác giả
                if ($('#author_avatar')[0].files[0]) {
                    formData.append('author_avatar', $('#author_avatar')[0].files[0]);
                }

                formData.append('publish_date', $('#publish_date').val());
                formData.append('category_id', $('#category_id').val());

                $.ajax({
                    url: '/views/admin/controller/articles.php',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(res) {
                        if (res.success) {
                            Swal.fire('Thành công!', '', 'success');
                            $('#modalCreate').modal('hide');
                            $('#articleForm')[0].reset();
                            loadArticles();
                        } else {
                            Swal.fire('Thất bại!', res.message || '', 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Thất bại!', 'Lỗi server!', 'error');
                    }
                });
            });

        });

        function loadCategories() {
            $.post('/views/admin/controller/articles.php', {
                action: 'getCategories'
            }, function(data) {
                let options = '<option value="">Chọn danh mục</option>';
                data.forEach(cat => {
                    options += `<option value="${cat.id}">${cat.name}</option>`;
                });
                $('#category_id').html(options);
            });
        }

        function loadArticles() {
            $.post('/views/admin/controller/articles.php', {
                action: 'get'
            }, function(data) {
                let rows = '';
                data.forEach(a => {
                    // Giới hạn excerpt và content, ví dụ 50 ký tự
                    let title = a.title.length > 20 ? a.title.substring(0, 20) + '...' : a.title;
                    let excerpt = a.excerpt.length > 20 ? a.excerpt.substring(0, 20) + '...' : a.excerpt;
                    let content = a.content.length > 20 ? a.content.substring(0, 20) + '...' : a.content;

                    // Cập nhật đường dẫn đầy đủ của ảnh bài viết
                    let imagePath = `/public/images/articles/${a.image_url}`;

                    rows += `<tr>
                <td>${a.id}</td>
                <td>${title}</td>
                <td>${excerpt}</td>
                <td>${content}</td>
                <td>${a.author_name}</td>
                <td><img src="${imagePath}" width="80"></td>
                <td>${a.publish_date}</td>
                <td>${a.category_name}</td>
                <td>
                    <button class="btn btn-warning btn-sm" onclick="editArticle(${a.id})">Sửa</button>
                    <button class="btn btn-danger btn-sm" onclick="deleteArticle(${a.id})">Xóa</button>
                </td>
            </tr>`;
                });
                $('#articleList').html(rows);
            });
        }

        function editArticle(id) {
            $.post('/views/admin/controller/articles.php', {
                action: 'getDetail',
                id
            }, function(a) {
                $('#modalTitle').text('Sửa bài viết');
                $('#articleId').val(a.id);
                $('#title').val(a.title);
                $('#excerpt').val(a.excerpt);
                $('#content').val(a.content);
                // $('#image_url').attr('src', `/public/images/articles/${a.image_url}`);
                $('#author_name').val(a.author_name);
                // $('#author_avatar').attr('src', `/public/images/authors/${a.author_avatar}`);
                $('#publish_date').val(a.publish_date);
                $('#category_id').val(parseInt(a.category_id));
                $('#modalCreate').modal('show');
            });
        }

        function deleteArticle(id) {
            Swal.fire({
                title: 'Bạn chắc chắn muốn xóa?',
                showCancelButton: true,
                confirmButtonText: 'Xóa',
                cancelButtonText: 'Hủy',
            }).then((result) => {
                if (result.isConfirmed) {
                    $.post('/views/admin/controller/articles.php', {
                        action: 'delete',
                        id
                    }, function(res) {
                        if (res.success) {
                            Swal.fire('Đã xóa!', '', 'success');
                            loadArticles();
                        } else {
                            Swal.fire('Xóa thất bại!', '', 'error');
                        }
                    });
                }
            });
        }
    </script>
</body>

</html>