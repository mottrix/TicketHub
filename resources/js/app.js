/* Shared dashboard entrypoint: conversation previews, sidebar controls, and theme switching. */
import "./ticket-conversation";

const sidebar = document.querySelector("#main-sidebar");
const sidebarToggle = document.querySelector("[data-sidebar-toggle]");
const sidebarClose = document.querySelector("[data-sidebar-close]");
const sidebarOverlay = document.querySelector("[data-sidebar-overlay]");

if (sidebar && sidebarToggle && sidebarClose && sidebarOverlay) {
    /* Keep the sidebar, overlay, toggle visibility, and screen-reader state in sync. */
    const mobileViewport = window.matchMedia("(max-width: 767px)");

    const setSidebarOpen = (open) => {

        sidebar.classList.toggle("hidden", !open);
        sidebar.classList.toggle("md:flex", open);
        sidebarToggle.classList.toggle("hidden", open);
        sidebarOverlay.classList.toggle("hidden", !open || !mobileViewport.matches);
        sidebarToggle.setAttribute("aria-expanded", String(open));
        if (open && mobileViewport.matches) {
            sidebarClose.focus();
        } else if (!open && sidebar.contains(document.activeElement)) {
            sidebarToggle.focus();
        }
    };

    sidebarToggle.addEventListener("click", () => setSidebarOpen(true));
    sidebarClose.addEventListener("click", () => setSidebarOpen(false));
    sidebarOverlay.addEventListener("click", () => setSidebarOpen(false));


    /* Following a sidebar link closes the drawer only on small screens. */
    sidebar.querySelectorAll("a").forEach((link) => {
        link.addEventListener("click", () => {
            if (mobileViewport.matches) {
                setSidebarOpen(false);
            }
        });
    });

    document.addEventListener("keydown", (event) => {
        if (event.key === "Tab" && mobileViewport.matches && !sidebar.classList.contains("hidden")) {
            const links = [...sidebar.querySelectorAll("button, a[href]")];
            const first = links[0];
            const last = links[links.length - 1];
            if (event.shiftKey && document.activeElement === first) {
                event.preventDefault();
                last.focus();
            } else if (!event.shiftKey && document.activeElement === last) {
                event.preventDefault();
                first.focus();
            }
        }
        if (
            event.key === "Escape" &&
            !sidebarOverlay.classList.contains("hidden")
        ) {
            setSidebarOpen(false);
            sidebarToggle.focus();
        }
    });

    mobileViewport.addEventListener("change", () => setSidebarOpen(!mobileViewport.matches));
    setSidebarOpen(!mobileViewport.matches);
}
const themeToggle = document.querySelector("#theme-toggle");
const moonIcon = document.querySelector("#moon-icon");
const sunIcon = document.querySelector("#sun-icon");

if (themeToggle && moonIcon && sunIcon) {
    /* The layout applies the initial theme before paint; this keeps the toggle in sync
       and remembers later changes under the same localStorage key. */
    let transitionTimeout;

    const updateIcons = () => {
        const isDark = document.documentElement.classList.contains("dark");

        moonIcon.classList.toggle("hidden", isDark);
        sunIcon.classList.toggle("hidden", !isDark);
        themeToggle.setAttribute("aria-pressed", String(isDark));
    };

    updateIcons();

    themeToggle.addEventListener("click", () => {
        const root = document.documentElement;
        root.classList.add("theme-transition");

        window.requestAnimationFrame(() => {
            const isDark = root.classList.toggle("dark");
            try {
                localStorage.setItem("theme", isDark ? "dark" : "light");
            } catch {}
            updateIcons();

            window.clearTimeout(transitionTimeout);
            transitionTimeout = window.setTimeout(() => {
                root.classList.remove("theme-transition");
            }, 450);
        });
    });
}
