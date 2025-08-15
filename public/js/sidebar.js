function formatDate(dateString) {
  const date = new Date(dateString);
  return date
    .toLocaleDateString("vi-VN", {
      day: "2-digit",
      month: "short",
      year: "numeric",
    })
    .replace("thg", "Thg")
    .replace(/\b(\w)/g, (s) => s.toUpperCase()); // Viết hoa chữ cái đầu
}
function renderEditorPicks(articles) {
  const container = $(".editor-picks");
  container
    .empty()
    .append('<h2 class="section-title mb-4">Editor\'s Picks</h2>');

  articles.forEach((article) => {
    const html = `
            <a style="text-decoration: none;" href="article-detail.php?id=${article.id}"><div class="small-article">
                <img src="/public/images/articles/${article.image_url}" alt="${
      article.title
    }" class="small-article-image">
                <div class="small-article-content">
                    <h4 class="small-article-title">${article.title}</h4>
                    <div class="small-article-meta">${
                      article.author_name
                    } • ${formatDate(article.publish_date)}</div>
                </div>
            </div></a>
        `;
    container.append(html);
  });
}
function renderHotArticles(articles) {
  const container = $(".hot-section");
  container
    .empty()
    .append('<h2 class="section-title mb-4">Bài hot trong tuần</h2>');

  articles.forEach((article, index) => {
    const rank = `#${index + 1}`;
    const html = `
     <a style="text-decoration: none;" href="article-detail.php?id=${article.id}">
            <div class="hot-item">
                <div class="hot-number">${rank}</div>
                <div class="hot-content">
                    <h6>${article.title}</h6>
                    <div class="hot-meta">${article.author_name} • ${formatDate(
      article.publish_date
    )}</div>
                </div>
            </div>
            </a>
        `;
    container.append(html);
  });
}
function renderTopAuthors(authors) {
  const container = $(".authors-sidebar");
  container.empty().append('<h3 class="section-title">Tác giả nổi bật</h3>');

  authors.forEach((author) => {
    let avatarHtml;
    if (author.avatar_url) {
      avatarHtml = `<img src="/public/images/authors/${author.avatar_url}" alt="${author.name}" class="author-avatar-large">`;
    } else {
      // Nếu là agency không có ảnh
      avatarHtml = `
                <div class="author-avatar-large bg-primary d-flex align-items-center justify-content-center text-white fw-bold">
                    ${author.name.charAt(0).toUpperCase()}
                </div>
            `;
    }

    const html = `
            <div class="author-item">
                ${avatarHtml}
                <div>
                    <div class="author-name">${author.name}</div>
                    <div class="author-title">${author.title}</div>
                </div>
            </div>
        `;
    container.append(html);
  });
}
//Load sidebar content
function loadSidebarContent() {
  $.ajax({
    url: "/views/admin/controller/articles.php",
    method: "POST",
    data: {
      action: "getFeaturedArticles",
      limit: 5,
    },
    dataType: "json",
    success: function (response) {
      console.log("👉 Dữ liệu nhận được:", response);

      // Kiểm tra nếu response là mảng và có dữ liệu
      if (Array.isArray(response) && response.length > 0) {
        // ✅ 1. Render Editor's Picks (dùng toàn bộ bài viết)
        console.log("Render Editor Picks");
        renderEditorPicks(response);

        // ✅ 2. Render Bài hot trong tuần (dùng cùng dữ liệu)
        console.log("Render Hot Articles");
        renderHotArticles(response);

        // ✅ 3. Tạo danh sách tác giả từ author_name trong bài viết
        const authorsMap = {};
        response.forEach((article) => {
          const name = article.author_name;
          if (name && !authorsMap[name]) {
            authorsMap[name] = {
              name: name,
              title: "Content Writer | Insightful", // Có thể lấy từ DB sau
              avatar_url: article.author_avatar, // tạm thời không có ảnh
            };
          }
        });
        const topAuthors = Object.values(authorsMap);
        console.log("Render Top Authors");
        renderTopAuthors(topAuthors);
      } else {
        console.warn("Dữ liệu từ API không hợp lệ hoặc rỗng");
      }
    },
  });
}
// Load trang khi document ready
$(document).ready(function () {
  loadSidebarContent();
});
