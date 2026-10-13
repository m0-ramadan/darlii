(() => {
    "use strict";

    function parseCookie(name) {
        const prefix = name + "=";
        const cookies = document.cookie.split(";");
        for (let i = 0; i < cookies.length; i++) {
            let c = cookies[i].trim();
            if (c.indexOf(prefix) === 0) {
                return c.substring(prefix.length, c.length);
            }
        }
        return null;
    }

    function setCookie(name, value, days) {
        let expires = "";
        if (days) {
            const date = new Date();
            date.setTime(date.getTime() + days * 24 * 60 * 60 * 1000);
            expires = "; expires=" + date.toUTCString();
        }
        const secure = window.location.protocol === "https:" ? "; Secure" : "";
        document.cookie = name + "=" + (value || "") + expires + "; path=/; SameSite=Lax" + secure;
    }

    function initAnnouncements() {
        const wrappers = document.querySelectorAll(".ae-anno-announcement-wrapper");
        if (!wrappers.length) return;

        let dismissedIds = [];
        try {
            dismissedIds = JSON.parse(parseCookie("ae-anno-dismissed-announcements") || "[]");
        } catch (e) {
            dismissedIds = [];
        }

        wrappers.forEach((wrapper) => {
            wrapper.setAttribute("data-initialized", "true");

            const allItems = Array.from(wrapper.querySelectorAll(".ae-anno-announcement"));
            // Filter out items that were dismissed
            const items = allItems.filter((item) => {
                const id = item.getAttribute("data-announcement-id");
                if (id && dismissedIds.includes(parseInt(id, 10))) {
                    item.style.display = "none";
                    return false;
                }
                return true;
            });

            if (!items.length) {
                if (wrapper.parentNode) {
                    wrapper.style.display = "none";
                }
                return;
            }

            const nextBtn = wrapper.querySelector(".ae-anno-announcement__next-button");
            const prevBtn = wrapper.querySelector(".ae-anno-announcement__previous-button");
            const dismissBtn = wrapper.querySelector(".ae-anno-announcement__dismiss-button");
            const autoplay = wrapper.getAttribute("data-announcement-autoplay") !== null;
            const delay = parseInt(wrapper.getAttribute("data-announcement-autoplay-delay") || "5000", 10);

            let currentIndex = 0;
            let timer = null;

            function showItem(index) {
                if (index >= items.length) {
                    currentIndex = 0;
                } else if (index < 0) {
                    currentIndex = items.length - 1;
                } else {
                    currentIndex = index;
                }

                items.forEach((item, idx) => {
                    if (idx === currentIndex) {
                        item.style.setProperty("display", "block", "important");
                        item.style.opacity = "1";
                    } else {
                        item.style.setProperty("display", "none", "important");
                    }
                });
            }

            function startAutoplay() {
                if (autoplay && delay > 0 && items.length > 1) {
                    stopAutoplay();
                    timer = setInterval(() => {
                        showItem(currentIndex + 1);
                    }, delay);
                }
            }

            function stopAutoplay() {
                if (timer) {
                    clearInterval(timer);
                    timer = null;
                }
            }

            // Show first item initially
            showItem(0);
            startAutoplay();

            // Next / Prev listeners
            if (nextBtn) {
                nextBtn.addEventListener("click", (e) => {
                    e.preventDefault();
                    showItem(currentIndex + 1);
                    startAutoplay();
                });
            }

            if (prevBtn) {
                prevBtn.addEventListener("click", (e) => {
                    e.preventDefault();
                    showItem(currentIndex - 1);
                    startAutoplay();
                });
            }

            // Pause on hover
            wrapper.addEventListener("mouseenter", stopAutoplay);
            wrapper.addEventListener("mouseleave", startAutoplay);

            // Dismiss listener
            if (dismissBtn) {
                dismissBtn.addEventListener("click", (e) => {
                    e.preventDefault();
                    try {
                        const idsToDismiss = JSON.parse(dismissBtn.getAttribute("data-announcement-ids") || "[]");
                        dismissedIds.push(...idsToDismiss);
                        setCookie("ae-anno-dismissed-announcements", JSON.stringify(dismissedIds), 365);
                    } catch (err) {
                        console.error("Error saving dismissed announcements:", err);
                    }
                    wrapper.style.display = "none";
                });
            }
        });
    }

    function checkLazyAndInit() {
        const lazyElement = document.querySelector('[data-bb-toggle="announcement-lazy-loading"]');
        if (lazyElement) {
            const url = lazyElement.getAttribute("data-url");
            if (url) {
                fetch(url, {
                    method: "GET",
                    headers: {
                        "Content-Type": "application/json",
                        Accept: "application/json"
                    }
                })
                    .then((res) => {
                        if (!res.ok) throw new Error("Network response was not ok");
                        return res.json();
                    })
                    .then((data) => {
                        if (data && data.data) {
                            lazyElement.outerHTML = data.data;
                            initAnnouncements();
                        }
                    })
                    .catch((err) => {
                        console.error("Announcement lazy load error:", err);
                        initAnnouncements();
                    });
                return;
            }
        }
        initAnnouncements();
    }

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", checkLazyAndInit);
    } else {
        checkLazyAndInit();
    }
})();