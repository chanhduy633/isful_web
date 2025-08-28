
class UniversalInteractiveSystem {
    constructor(options = {}) {
        this.options = {
            toastContainer: "#toast-container",
            loginModal: "#loginModal",
            loginMessage: "#loginMessage",
            autoLoadDelay: 600, 
            ...options
        };

        this.init();
    }

    init() {
        this.createToastContainer();
        this.bindEvents();
        
        setTimeout(() => {
            this.autoLoadStatus();
        }, this.options.autoLoadDelay);
    }

    createToastContainer() {
        if ($(this.options.toastContainer).length === 0) {
            $("body").append('<div id="toast-container"></div>');
        }
    }

    bindEvents() {
        $(document).on("click", ".like-btn, .engagement-button.like-btn", (e) => {
            e.preventDefault();
            e.stopPropagation();
            const articleId = $(e.currentTarget).data("article-id");
            if (articleId) {
                this.toggleLike(articleId, e.currentTarget);
            }
        });

        $(document).on(
            "click",
            ".bookmark-btn, .engagement-button.bookmark-btn",
            (e) => {
                e.preventDefault();
                e.stopPropagation();
                const articleId = $(e.currentTarget).data("article-id");
                if (articleId) {
                    this.toggleBookmark(articleId, e.currentTarget);
                }
            }
        );

        $(document).on("click", ".share-btn, .engagement-button.share-btn", (e) => {
            e.preventDefault();
            e.stopPropagation();
            const articleId = $(e.currentTarget).data("article-id");
            const title = $(e.currentTarget).data("title");
            const excerpt = $(e.currentTarget).data("excerpt");
            if (articleId) {
                this.shareArticle(articleId, title, excerpt);
            }
        });

        $(document).on("click", "#sticky-like-btn", (e) => {
            e.preventDefault();
            const articleId = $(e.currentTarget).data("article-id") || this.getArticleIdFromPage();
            if (articleId) {
                this.toggleLike(articleId, e.currentTarget);
            }
        });

        $(document).on("click", "#sticky-bookmark-btn", (e) => {
            e.preventDefault();
            const articleId = $(e.currentTarget).data("article-id") || this.getArticleIdFromPage();
            if (articleId) {
                this.toggleBookmark(articleId, e.currentTarget);
            }
        });

        $(document).on("click", "#sticky-share-btn", (e) => {
            e.preventDefault();
            const articleId = $(e.currentTarget).data("article-id") || this.getArticleIdFromPage();
            if (articleId) {
                this.shareArticle(articleId);
            }
        });
    }

    getArticleIdFromPage() {
        const urlParams = new URLSearchParams(window.location.search);
        return urlParams.get("id");
    }

    async toggleLike(articleId, button) {
        const $button = $(button);
        if ($button.hasClass("loading")) return;

        this.setLoading($button, true);

        try {
            const response = await fetch("/controller/interactive.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded",
                },
                body: `action=toggle&article_id=${articleId}`,
            });

            const data = await response.json();

            if (data.success) {
                this.updateLikeUI(articleId, data.likes_count, data.user_liked);
                this.showToast(data.message, "success");
            } else {
                if (data.require_login) {
                    this.showLoginPrompt();
                } else {
                    this.showToast(data.message || "Có lỗi xảy ra", "error");
                }
            }
        } catch (error) {
            console.error("Like error:", error);
            this.showToast("Không thể kết nối đến server", "error");
        }

        this.setLoading($button, false);
    }

    async toggleBookmark(articleId, button) {
        const $button = $(button);
        if ($button.hasClass("loading")) return;

        this.setLoading($button, true);

        try {
            const response = await fetch("/controller/interactive.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded",
                },
                body: `action=toggleSave&article_id=${articleId}`,
            });

            const data = await response.json();

            if (data.success) {
                this.updateBookmarkUI(articleId, data.saved);
                this.showToast(data.message, "success");
            } else {
                if (data.require_login) {
                    this.showLoginPrompt();
                } else {
                    this.showToast(data.message || "Có lỗi xảy ra", "error");
                }
            }
        } catch (error) {
            console.error("Bookmark error:", error);
            this.showToast("Không thể kết nối đến server", "error");
        }

        this.setLoading($button, false);
    }

    shareArticle(articleId, title, excerpt) {
        if (!title) {
            title = document.title || "Bài viết hay";
        }
        if (!excerpt) {
            excerpt =
                $('meta[name="description"]').attr("content") ||
                "Đọc bài viết thú vị này";
        }

        const url = articleId
            ? `${window.location.origin}/article-detail.php?id=${articleId}`
            : window.location.href;

        if (navigator.share) {
            navigator
                .share({
                    title: title,
                    text: excerpt,
                    url: url,
                })
                .then(() => {
                    this.showToast("Đã chia sẻ thành công", "success");
                })
                .catch((error) => {
                    if (error.name !== "AbortError") {
                        console.log("Share error:", error);
                        this.fallbackShare(url);
                    }
                });
        } else {
            this.fallbackShare(url);
        }
    }

    fallbackShare(url) {
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard
                .writeText(url)
                .then(() => {
                    this.showToast("Link đã được sao chép vào clipboard!", "success");
                })
                .catch(() => {
                    this.legacyCopyToClipboard(url);
                });
        } else {
            this.legacyCopyToClipboard(url);
        }
    }

    legacyCopyToClipboard(url) {
        const textArea = document.createElement("textarea");
        textArea.value = url;
        textArea.style.position = "fixed";
        textArea.style.left = "-999999px";
        textArea.style.top = "-999999px";
        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();

        try {
            document.execCommand("copy");
            this.showToast("Link đã được sao chép!", "success");
        } catch (err) {
            console.error("Copy failed:", err);
            this.showToast("Không thể sao chép link", "error");
        }

        document.body.removeChild(textArea);
    }

    updateLikeUI(articleId, likesCount, userLiked) {
        const likeButtons = $(
            `.like-btn[data-article-id="${articleId}"], #like-btn[data-article-id="${articleId}"], #sticky-like-btn[data-article-id="${articleId}"], #sticky-like-btn`
        );

        likeButtons.each(function () {
            const $btn = $(this);
            const $icon = $btn.find("i");
            const $countSpan = $btn.find(".likes-count");
            const $textSpan = $btn.find("span").not(".likes-count");

            if ($countSpan.length) {
                $countSpan.text(likesCount);
            }
            if (userLiked) {
                $btn.addClass("liked active");
                if ($textSpan.length && ($textSpan.text().includes("Thích") || $textSpan.text().includes("thích"))) {
                    if ($countSpan.length) {
                        $textSpan.html(`Đã thích (<span class="likes-count">${likesCount}</span>)`);
                    } else {
                        $textSpan.text("Đã thích");
                    }
                }
            } else {
                $btn.removeClass("liked active");

                if ($textSpan.length && ($textSpan.text().includes("thích") || $textSpan.text().includes("Thích"))) {
                    if ($countSpan.length) {
                        $textSpan.html(`Thích (<span class="likes-count">${likesCount}</span>)`);
                    } else {
                        $textSpan.text("Thích");
                    }
                }
            }
        });
    }

    updateBookmarkUI(articleId, isBookmarked) {
        const bookmarkButtons = $(
            `.bookmark-btn[data-article-id="${articleId}"], #bookmark-btn[data-article-id="${articleId}"], #sticky-bookmark-btn[data-article-id="${articleId}"], #sticky-bookmark-btn`
        );

        bookmarkButtons.each(function () {
            const $btn = $(this);
            const $icon = $btn.find("i");
            const $textSpan = $btn.find("span").not(".count");

            if (isBookmarked) {
                $btn.addClass("bookmarked active");

                if ($textSpan.length) {
                    $textSpan.text("Đã lưu");
                }
            } else {
                $btn.removeClass("bookmarked active");

                if ($textSpan.length) {
                    $textSpan.text("Lưu");
                }
            }
        });
    }

    setLoading($button, loading) {
        if (loading) {
            $button.addClass("loading").prop("disabled", true);
            const $icon = $button.find("i");
            const originalIcon = $icon.attr("class");
            $button.data("original-icon", originalIcon);
            $icon.attr("class", "fas fa-spinner fa-spin");
        } else {
            $button.removeClass("loading").prop("disabled", false);
            const originalIcon = $button.data("original-icon");
            if (originalIcon) {
                $button.find("i").attr("class", originalIcon);
            }
        }
    }

    showLoginPrompt() {
        try {
            const messageDiv = $(this.options.loginMessage);
            if (messageDiv.length) {
                messageDiv.html(
                    '<div class="alert alert-info mb-0">Vui lòng đăng nhập để sử dụng tính năng này.</div>'
                );
            }

            const loginModal = $(this.options.loginModal);
            if (loginModal.length && typeof bootstrap !== "undefined") {
                const modal = new bootstrap.Modal(loginModal[0]);
                modal.show();
            } else {
                this.showToast("Vui lòng đăng nhập để sử dụng tính năng này", "info");
            }
        } catch (error) {
            console.error("Login prompt error:", error);
            this.showToast("Vui lòng đăng nhập để sử dụng tính năng này", "info");
        }
    }

    showToast(message, type = "success") {
        const toastId = "toast-" + Date.now();
        const toast = $(`
            <div class="toast ${type}" id="${toastId}">
                <div class="toast-message">${message}</div>
            </div>
        `);

        $(this.options.toastContainer).append(toast);

        setTimeout(() => {
            toast.addClass("show");
        }, 100);

        setTimeout(() => {
            toast.removeClass("show");
            setTimeout(() => {
                toast.remove();
            }, 300);
        }, 3000);
    }

    // (for article-detail page)
    async loadSingleArticleStatus(articleId) {
        if (!articleId) return;

        try {
            // Load both like and save status in one bulk request
            const response = await fetch("/controller/interactive.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded",
                },
                body: `action=getBulkStatus&article_ids=${articleId}`,
            });

            const result = await response.json();

            if (result.success && result.data && result.data.length > 0) {
                const articleData = result.data[0];
                this.updateLikeUI(
                    articleData.article_id,
                    articleData.likes_count,
                    articleData.user_liked
                );
                this.updateBookmarkUI(articleData.article_id, articleData.user_saved);
            }
        } catch (error) {
            console.error("Load single article status error:", error);
        }
    }

    //(for listing pages)
    async loadBulkArticleStatus(articleIds) {
        if (!articleIds || articleIds.length === 0) return;

        const cleanIds = [...new Set(articleIds)].filter(
            (id) => id && parseInt(id) > 0
        );
        if (cleanIds.length === 0) return;

        try {
            const response = await fetch("/controller/interactive.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded",
                },
                body: `action=getBulkStatus&article_ids=${cleanIds.join(",")}`,
            });

            const result = await response.json();

            if (result.success && result.data) {
                result.data.forEach((item) => {
                    this.updateLikeUI(item.article_id, item.likes_count, item.user_liked);
                    this.updateBookmarkUI(item.article_id, item.user_saved);
                });
            }
        } catch (error) {
            console.error("Load bulk status error:", error);
        }
    }

    autoLoadStatus() {
        const articleId = this.getArticleIdFromPage();

        if (articleId) {
            this.loadSingleArticleStatus(articleId);
        } else {
            const articleIds = this.collectArticleIdsFromPage();
            if (articleIds.length > 0) {
                this.loadBulkArticleStatus(articleIds);
            }
        }
    }

    collectArticleIdsFromPage() {
        const articleIds = [];
        $(".like-btn[data-article-id], .bookmark-btn[data-article-id], .share-btn[data-article-id]").each(function () {
            const id = $(this).data("article-id");
            if (id && articleIds.indexOf(id) === -1) {
                articleIds.push(id);
            }
        });
        return articleIds;
    }

    refreshStatus(delay = 500) {
        setTimeout(() => {
            this.autoLoadStatus();
        }, delay);
    }
}

$(document).ready(function () {
    if (typeof window.interactiveSystem === "undefined") {
        window.interactiveSystem = new UniversalInteractiveSystem();
        
        console.log("Interactive system initialized");
    }
});

function reloadInteractiveStatus(delay = 500) {
    if (window.interactiveSystem) {
        window.interactiveSystem.refreshStatus(delay);
    }
}

window.refreshInteractiveStatus = reloadInteractiveStatus;