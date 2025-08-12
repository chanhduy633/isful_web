const searchBtn = document.querySelector(".search-icon-btn");
const searchInput = searchBtn.querySelector(".search-input");
const searchResults = document.querySelector(".search-results");

// Hàm gọi AJAX tìm kiếm
function performSearch(keyword) {
  if (keyword.trim() === "") {
    searchResults.innerHTML = "";
    searchResults.classList.remove("active");
    return;
  }

  fetch('/views/admin/controller/articles.php', {  // Thay đúng đường dẫn tới file PHP xử lý
    method: 'POST',
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded',
    },
    body: 'action=search&keyword=' + encodeURIComponent(keyword)
  })
  .then(response => response.json())
  .then(data => {
    searchResults.innerHTML = "";

    if (Array.isArray(data) && data.length > 0) {
      data.forEach(article => {
        const item = document.createElement("a");
        item.href = `article-detail.php?id=${article.id}`; // Thay link chi tiết nếu có
        item.className = "result-item";

        // Đảm bảo có ảnh, nếu không thì dùng ảnh mặc định
        const imageUrl = article.image_url 
          ? `../../../public/images/articles/${article.image_url}` 
          : '/default-image.jpg';

        item.innerHTML = `
          <img src="${imageUrl}" alt="${article.title}">
          <div class = "article-title">${article.title}</div>
        `;
        searchResults.appendChild(item);
      });
      searchResults.classList.add("active");
    } else {
      searchResults.innerHTML = '<div class="no-result">Không tìm thấy bài viết nào.</div>';
      searchResults.classList.add("active");
    }
  })
  .catch(err => {
    console.error("Lỗi tìm kiếm:", err);
    searchResults.innerHTML = '<div class="no-result">Có lỗi xảy ra khi tìm kiếm.</div>';
    searchResults.classList.add("active");
  });
}

// Sự kiện gõ tìm kiếm
searchInput.addEventListener("input", function (e) {
  const keyword = e.target.value;
  performSearch(keyword);
});

// Bật/tắt khi click vào nút
searchBtn.addEventListener("click", function (e) {
  e.stopPropagation();
  this.classList.toggle("active");

  if (this.classList.contains("active")) {
    searchInput.focus();
    // Nếu đã có từ khóa, tìm lại
    if (searchInput.value.trim()) {
      performSearch(searchInput.value.trim());
    }
  }
  searchResults.classList.remove("active");

});

// Đóng khi click ra ngoài
document.addEventListener("click", function () {
  searchResults.classList.remove("active");
  searchBtn.classList.remove("active");
});

// Ngăn sự kiện nổi bọt khi click vào input hoặc kết quả
searchInput.addEventListener("click", function (e) {
  e.stopPropagation();
});

searchResults.addEventListener("click", function (e) {
  e.stopPropagation();
});