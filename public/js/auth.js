// Xử lý đăng nhập
$("#loginForm").on("submit", function (e) {
  e.preventDefault();

  const formData = new FormData(this);
  formData.append("action", "login");

  $.ajax({
    url: "",
    method: "POST",
    data: formData,
    processData: false,
    contentType: false,
    dataType: "json",
    success: function (response) {
      if (response.success) {
        window.location.href = response.redirect;
      } else {
        $("#loginMessage").html(
          '<div class="alert alert-danger">' + response.error + "</div>"
        );
      }
    },
    error: function () {
      $("#loginMessage").html(
        '<div class="alert alert-danger">Có lỗi xảy ra. Vui lòng thử lại!</div>'
      );
    },
  });
});

// Xử lý đăng ký
$("#registerForm").on("submit", function (e) {
  e.preventDefault();

  const formData = new FormData(this);
  formData.append("action", "register");

  $.ajax({
    url: "",
    method: "POST",
    data: formData,
    processData: false,
    contentType: false,
    dataType: "json",
    success: function (response) {
      if (response.success) {
        $("#registerMessage").html(
          '<div class="alert alert-success">' + response.message + "</div>"
        );
        setTimeout(function () {
          $("#registerModal").modal("hide");
          $("#loginModal").modal("show");
        }, 2000);
      } else {
        $("#registerMessage").html(
          '<div class="alert alert-danger">' + response.error + "</div>"
        );
      }
    },
    error: function () {
      $("#registerMessage").html(
        '<div class="alert alert-danger">Có lỗi xảy ra. Vui lòng thử lại!</div>'
      );
    },
  });
});

// Xử lý Google Sign-In
function handleCredentialResponse(response) {
  const formData = new FormData();
  formData.append("action", "google_login");
  formData.append("google_token", response.credential);

  $.ajax({
    url: "",
    method: "POST",
    data: formData,
    processData: false,
    contentType: false,
    dataType: "json",
    success: function (response) {
      if (response.success) {
        window.location.href = response.redirect;
      } else {
        $("#loginMessage").html(
          '<div class="alert alert-danger">' + response.error + "</div>"
        );
      }
    },
    error: function () {
      $("#loginMessage").html(
        '<div class="alert alert-danger">Có lỗi xảy ra khi đăng nhập với Google!</div>'
      );
    },
  });
}

// Reset form khi mở modal
$(".modal").on("show.bs.modal", function () {
  const form = $(this).find("form")[0];
if (form) {
    form.reset();
}
  $(this).find(".alert").remove();
});

// Validate password confirmation
$("#reg_confirm_password").on("input", function () {
  const password = $("#reg_password").val();
  const confirmPassword = $(this).val();

  if (password !== confirmPassword) {
    this.setCustomValidity("Mật khẩu xác nhận không khớp");
  } else {
    this.setCustomValidity("");
  }
});
