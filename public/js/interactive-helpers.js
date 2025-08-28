
function escapeHtml(text) {
    if (!text) return '';
    return text.toString()
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

// (for article cards)
function generateEngagementStats(article, options = {}) {
    const {
        showText = false,
        size = 'normal',
        theme = 'light',
        userLiked = false,
        userSaved = false
    } = options;

    const likesCount = article.likes || 0;
    const articleId = article.id;
    const title = escapeHtml(article.title);
    const excerpt = escapeHtml(article.excerpt);

    const sizeClass = size === 'small' ? 'small' : size === 'large' ? 'large' : '';
    const textClass = showText ? 'with-text' : '';

    return `
        <div class="engagement-stats ${sizeClass} ${textClass}">
            <button class="engagement-button like-btn" 
                    data-article-id="${articleId}">
                <i class="fas fa-heart"></i>
                <span class="likes-count">${likesCount}</span>
                ${showText ? `<span class="btn-text">Thích</span>` : ''}
            </button>
            <button class="engagement-button bookmark-btn"
                    data-article-id="${articleId}">
                <i class="fas fa-bookmark"></i>
                ${showText ? `<span class="btn-text">Lưu</span>` : ''}
            </button>
            <button class="engagement-button share-btn" 
                    data-article-id="${articleId}"
                    data-title="${title}"
                    data-excerpt="${excerpt}">
                <i class="fas fa-share"></i>
                ${showText ? '<span class="btn-text">Chia sẻ</span>' : ''}
            </button>
        </div>
    `;
}

function generateDetailedEngagementActions(article) {
    const likesCount = article.likes || 0;
    const articleId = article.id;
    const title = escapeHtml(article.title);
    const excerpt = escapeHtml(article.excerpt);

    return `
        <div class="engagement-actions">
            <button class="engagement-button like-btn" id="like-btn" data-article-id="${articleId}">
                <i class="fas fa-heart"></i>
                <span>Thích (<span class="likes-count">${likesCount}</span>)</span>
            </button>
            <button class="engagement-button bookmark-btn" id="bookmark-btn" data-article-id="${articleId}">
                <i class="fas fa-bookmark"></i>
                <span>Lưu</span>
            </button>
            <button class="engagement-button share-btn" id="share-btn" 
                    data-article-id="${articleId}"
                    data-title="${title}"
                    data-excerpt="${excerpt}">
                <i class="fas fa-share"></i>
                <span>Chia sẻ</span>
            </button>
        </div>
    `;
}

// (for article detail page)
function generateStickySidebarActions(article) {
    const articleId = article.id;
    
    return `
        <div class="author-sticky-sidebar">
            <div class="author-sticky-content">
                <img src="/public/images/authors/${escapeHtml(article.author_avatar)}"
                    alt="${escapeHtml(article.author_name)}"
                    class="author-sticky-avatar">
                <div class="author-sticky-name">
                    ${escapeHtml(article.author_name)}
                </div>
                <div class="author-sticky-actions">
                    <button class="action-btn" id="sticky-like-btn" 
                            data-article-id="${articleId}" title="Thích">
                        <i class="fas fa-heart"></i>
                    </button>
                    <button class="action-btn" id="sticky-bookmark-btn" 
                            data-article-id="${articleId}" title="Lưu bài viết">
                        <i class="fas fa-bookmark"></i>
                    </button>
                    <button class="action-btn" id="sticky-share-btn" 
                            data-article-id="${articleId}" title="Chia sẻ">
                        <i class="fas fa-share"></i>
                    </button>
                </div>
            </div>
        </div>
    `;
}

function formatDate(dateString) {
    const date = new Date(dateString);
    const options = {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    };
    return date.toLocaleDateString('vi-VN', options);
}

function createMainArticleHTML(article) {
    return `
        <div class="col-12 col-md-8 mt-24">
            <div class="main-article">
                <img class="w-100" src="/public/images/articles/${escapeHtml(article.image_url)}" alt="${escapeHtml(article.title)}">
                <a href="article-detail.php?id=${article.id}" class="overlay-bg"></a>
                <div class="thumbnail-describe">
                    <a href="article-detail.php?id=${article.id}">
                        <h2 class="line-clamp-2">${escapeHtml(article.title)}</h2>
                    </a> 
                    <h4 class="line-clamp-2">${escapeHtml(article.excerpt)}</h4>
                    <div class="article-meta">
                        <div class="author-info">
                            <img src="/public/images/authors/${escapeHtml(article.author_avatar)}" alt="Author" class="author-avatar">
                            <div>
                                <div style="color: white; font-weight: 500;">${escapeHtml(article.author_name)}</div>
                                <div style="color: #ccc;">${formatDate(article.publish_date)}</div>
                            </div>
                        </div>
                        ${generateEngagementStats(article, { theme: 'dark' })}
                    </div>
                </div>
            </div>
        </div>
    `;
}

function collectArticleIdsFromPage() {
    const articleIds = [];
    $('.like-btn[data-article-id], .bookmark-btn[data-article-id], .share-btn[data-article-id]').each(function() {
        const id = $(this).data('article-id');
        if (id && articleIds.indexOf(id) === -1) {
            articleIds.push(id);
        }
    });
    return articleIds;
}

function refreshInteractiveStatus(delay = 500) {
    setTimeout(() => {
        const articleIds = collectArticleIdsFromPage();
        if (articleIds.length > 0 && window.interactiveSystem) {
            window.interactiveSystem.loadBulkArticleStatus(articleIds);
        }
    }, delay);
}