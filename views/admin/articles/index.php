<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý bài viết với Rich Text Editor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Thêm Quill.js CSS -->
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">

    <style>
        /* Custom styles for content display */
        .content-preview {
            max-height: 100px;
            overflow: hidden;
            position: relative;
        }

        .content-preview::after {
            content: "";
            position: absolute;
            bottom: 0;
            left: 0;
            height: 30px;
            width: 100%;
            background: linear-gradient(transparent, white);
        }

        .full-content {
            max-height: none;
        }

        .full-content::after {
            display: none;
        }

        /* Highlight styles */
        .highlight-yellow {
            background-color: #ffeb3b;
            padding: 2px 4px;
        }

        .highlight-green {
            background-color: #4caf50;
            color: white;
            padding: 2px 4px;
        }

        .highlight-blue {
            background-color: #2196f3;
            color: white;
            padding: 2px 4px;
        }

        /* Rich text editor container */
        #editor-container {
            height: 300px;
        }

        /* Table responsive */
        .table-container {
            overflow-x: auto;
        }

        .content-cell {
            min-width: 200px;
            max-width: 300px;
        }

        #fullContentView img {
            width: 100%;
            border-radius: 8px;
        }
    </style>
</head>

<body>
    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
            <h2>Danh sách bài viết</h2>
            <button class="btn btn-primary" id="btnAdd">Thêm bài viết</button>
        </div>

        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Tiêu đề</th>
                        <th>Trích dẫn</th>
                        <th class="content-cell">Nội dung</th>
                        <th>Tác giả</th>
                        <th>Hình ảnh</th>
                        <th>Ngày đăng</th>
                        <th>Danh mục</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody id="articleList">
                    <tr>
                        <td colspan="9">Loading...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal thêm/sửa -->
    <div class="modal fade" id="modalCreate">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 id="modalTitle">Thêm bài viết</h5>
                    <button class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="articleForm" enctype="multipart/form-data">
                        <input type="hidden" id="articleId">

                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label">Tiêu đề</label>
                                <input class="form-control mb-3" id="title" placeholder="Nhập tiêu đề bài viết">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Ngày đăng</label>
                                <input class="form-control mb-3" type="date" id="publish_date">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Trích dẫn</label>
                            <textarea class="form-control" id="excerpt" rows="2" placeholder="Nhập trích dẫn ngắn"></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Nội dung bài viết</label>
                            <div id="editor-container"></div>
                            <textarea id="content" style="display: none;"></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label">Ảnh bài viết</label>
                                <input type="file" class="form-control mb-3" id="image_url" accept="image/*">
                                <div id="current-image" style="display: none;">
                                    <small class="text-muted">Ảnh hiện tại:</small>
                                    <img id="current-image-preview" src="" style="max-width: 100px; max-height: 100px; object-fit: cover;" class="d-block mt-1">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Avatar tác giả</label>
                                <input type="file" class="form-control mb-3" id="author_avatar" accept="image/*">
                                <div id="current-avatar" style="display: none;">
                                    <small class="text-muted">Avatar hiện tại:</small>
                                    <img id="current-avatar-preview" src="" style="max-width: 100px; max-height: 100px; object-fit: cover;" class="d-block mt-1">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label">Tên tác giả</label>
                                <input class="form-control mb-3" id="author_name" placeholder="Nhập tên tác giả">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Danh mục</label>
                                <select class="form-control mb-3" id="category_id">
                                    <option value="">Chọn danh mục</option>
                                </select>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button class="btn btn-success" id="saveBtn">Lưu bài viết</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal xem nội dung đầy đủ -->
    <div class="modal fade" id="modalViewContent">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5>Nội dung bài viết</h5>
                    <button class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="fullContentView">
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Thêm Quill.js -->
    <script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>

    <script>
        let quill;

        $(document).ready(function() {
            // Khởi tạo Quill Editor
            initQuillEditor();

            loadCategories();
            loadArticles();

            $('#btnAdd').click(function() {
                $('#modalTitle').text('Thêm bài viết mới');
                $('#articleForm')[0].reset();
                $('#articleId').val('');
                quill.setContents([]);
                $('#current-image').hide();
                $('#current-avatar').hide();
                $('#modalCreate').modal('show');
            });

            $('#saveBtn').click(function() {
                // Lấy nội dung HTML từ Quill editor
                const htmlContent = quill.root.innerHTML;
                $('#content').val(htmlContent);

                let articleId = $('#articleId').val().trim();
                let action = articleId ? 'update' : 'create';

                let formData = new FormData();
                formData.append('action', action);
                formData.append('id', articleId);
                formData.append('title', $('#title').val());
                formData.append('excerpt', $('#excerpt').val());
                formData.append('content', htmlContent);

                if ($('#image_url')[0].files[0]) {
                    formData.append('image_url', $('#image_url')[0].files[0]);
                }

                formData.append('author_name', $('#author_name').val());

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
                            quill.setContents([]);
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

        function initQuillEditor() {
            const toolbarOptions = [
                ['bold', 'italic', 'underline', 'strike'], // toggled buttons
                ['blockquote', 'code-block'],

                [{
                    'header': 1
                }, {
                    'header': 2
                }], // custom button values
                [{
                    'list': 'ordered'
                }, {
                    'list': 'bullet'
                }],
                [{
                    'script': 'sub'
                }, {
                    'script': 'super'
                }], // superscript/subscript
                [{
                    'indent': '-1'
                }, {
                    'indent': '+1'
                }], // outdent/indent
                [{
                    'direction': 'rtl'
                }], // text direction

                [{
                    'size': ['small', false, 'large', 'huge']
                }], // custom dropdown
                [{
                    'header': [1, 2, 3, 4, 5, 6, false]
                }],

                [{
                    'color': []
                }, {
                    'background': []
                }], // dropdown with defaults from theme
                [{
                    'font': []
                }],
                [{
                    'align': []
                }],

                ['link', 'image', 'video'],
                ['clean'] // remove formatting button
            ];

            quill = new Quill('#editor-container', {
                modules: {
                    toolbar: toolbarOptions
                },
                placeholder: 'Nhập nội dung bài viết...',
                theme: 'snow'
            });
        }

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
                    let title = a.title.length > 30 ? a.title.substring(0, 30) + '...' : a.title;
                    let excerpt = a.excerpt.length > 50 ? a.excerpt.substring(0, 50) + '...' : a.excerpt;

                    // Hiển thị nội dung HTML với preview và nút xem thêm
                    let contentPreview = stripHtml(a.content).length > 100 ?
                        stripHtml(a.content).substring(0, 100) + '...' :
                        stripHtml(a.content);

                    let imagePath = `/public/images/articles/${a.image_url}`;

                    rows += `<tr>
                        <td>${a.id}</td>
                        <td>${title}</td>
                        <td>${excerpt}</td>
                        <td class="content-cell">
                            <div class="content-preview mb-2">${contentPreview}</div>
                            <button class="btn btn-sm btn-outline-info" onclick="viewFullContent('${escapeHtml(a.content)}', '${escapeHtml(a.title)}')">
                                Xem đầy đủ
                            </button>
                        </td>
                        <td>${a.author_name}</td>
                        <td><img src="${imagePath}" style="width: 60px; height: 60px; object-fit: cover;"></td>
                        <td>${formatDate(a.publish_date)}</td>
                        <td><span class="badge bg-primary">${a.category_name}</span></td>
                        <td>
                            <div class="btn-group-vertical">
                                <button class="btn btn-warning btn-sm mb-1" onclick="editArticle(${a.id})">Sửa</button>
                                <button class="btn btn-danger btn-sm" onclick="deleteArticle(${a.id})">Xóa</button>
                            </div>
                        </td>
                    </tr>`;
                });
                $('#articleList').html(rows);
            });
        }

        function stripHtml(html) {
            const tmp = document.createElement("DIV");
            tmp.innerHTML = html;
            return tmp.textContent || tmp.innerText || "";
        }

        function escapeHtml(unsafe) {
            return unsafe
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }

        function formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('vi-VN');
        }

        function viewFullContent(content, title) {
            $('#modalViewContent .modal-title').text(title);
            $('#fullContentView').html(content);
            $('#modalViewContent').modal('show');
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

                // Set nội dung vào Quill editor
                quill.root.innerHTML = a.content;

                $('#author_name').val(a.author_name);
                $('#publish_date').val(a.publish_date);
                $('#category_id').val(parseInt(a.category_id));

                // Hiển thị ảnh hiện tại
                if (a.image_url) {
                    $('#current-image').show();
                    $('#current-image-preview').attr('src', `/public/images/articles/${a.image_url}`);
                }

                if (a.author_avatar) {
                    $('#current-avatar').show();
                    $('#current-avatar-preview').attr('src', `/public/images/authors/${a.author_avatar}`);
                }

                $('#modalCreate').modal('show');
            });
        }

        function deleteArticle(id) {
            Swal.fire({
                title: 'Bạn chắc chắn muốn xóa?',
                text: 'Hành động này không thể hoàn tác!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Xóa',
                cancelButtonText: 'Hủy'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.post('/views/admin/controller/articles.php', {
                        action: 'delete',
                        id
                    }, function(res) {
                        if (res.success) {
                            Swal.fire('Đã xóa!', 'Bài viết đã được xóa.', 'success');
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