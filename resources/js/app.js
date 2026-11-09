import "./ticket-conversation";

const sidebar = document.querySelector("#main-sidebar");
const sidebarToggle = document.querySelector("[data-sidebar-toggle]");
const sidebarClose = document.querySelector("[data-sidebar-close]");
const sidebarOverlay = document.querySelector("[data-sidebar-overlay]");
const sidebarCreateTicket = document.querySelector(
    "[data-sidebar-create-ticket]",
);
const sidebarMyTickets = document.querySelector("[data-sidebar-my-ticket]");

if (sidebar && sidebarToggle && sidebarClose && sidebarOverlay) {
    const mobileViewport = window.matchMedia("(max-width: 767px)");

    const setSidebarOpen = (open) => {

        
        sidebar.classList.toggle("hidden", !open);
        sidebarToggle.classList.toggle("hidden", open);
        sidebarOverlay.classList.toggle("hidden", !open);
        sidebarToggle.setAttribute("aria-expanded", String(open));
    };

    sidebarToggle.addEventListener("click", () => setSidebarOpen(true));
    sidebarClose.addEventListener("click", () => setSidebarOpen(false));
    sidebarOverlay.addEventListener("click", () => setSidebarOpen(false));

    // sidebarCreateTicket.addEventListener("click", () => {setSidebarOpen(false)});
    // sidebarMyTickets.addEventListener("click", () => {setSidebarOpen(false)});

    sidebar.querySelectorAll("a").forEach((link) => {
        link.addEventListener("click", () => {
            if (mobileViewport.matches) {
                setSidebarOpen(false);
            }
        });
    });

    document.addEventListener("keydown", (event) => {
        if (
            event.key === "Escape" &&
            !sidebarOverlay.classList.contains("hidden")
        ) {
            setSidebarOpen(false);
            sidebarToggle.focus();
        }
    });

    setSidebarOpen(true);
}
const themeToggle = document.querySelector("#theme-toggle");
const moonIcon = document.querySelector("#moon-icon");
const sunIcon = document.querySelector("#sun-icon");

if (themeToggle && moonIcon && sunIcon) {
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
            localStorage.setItem("theme", isDark ? "dark" : "light");
            updateIcons();

            window.clearTimeout(transitionTimeout);
            transitionTimeout = window.setTimeout(() => {
                root.classList.remove("theme-transition");
            }, 450);
        });
    });
}
