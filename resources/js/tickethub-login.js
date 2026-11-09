(() => {
    const root = document.documentElement;
    const themeButton = document.querySelector("[data-theme-toggle]");
    const media = matchMedia("(prefers-color-scheme: dark)");
    let explicitTheme = false;
    try {
        explicitTheme = ["light", "dark"].includes(
            localStorage.getItem("tickethub-theme"),
        );
    } catch {}
    function applyTheme(theme) {
        root.dataset.thTheme = theme;
        const dark = theme === "dark";
        themeButton?.setAttribute(
            "aria-label",
            `Switch to ${dark ? "light" : "dark"} theme`,
        );
        themeButton?.setAttribute("aria-pressed", String(dark));
        const icon = document.querySelector("[data-theme-icon]");
        if (icon) icon.textContent = dark ? "☀" : "☾";
    }
    applyTheme(root.dataset.thTheme || (media.matches ? "dark" : "light"));
    themeButton?.addEventListener("click", () => {
        const theme = root.dataset.thTheme === "dark" ? "light" : "dark";
        explicitTheme = true;
        applyTheme(theme);
        try {
            localStorage.setItem("tickethub-theme", theme);
        } catch {}
    });
    media.addEventListener("change", (event) => {
        if (!explicitTheme) applyTheme(event.matches ? "dark" : "light");
    });
    const password = document.getElementById("password");
    const toggle = document.querySelector("[data-password-toggle]");
    toggle?.addEventListener("click", () => {
        const show = password.type === "password";
        password.type = show ? "text" : "password";
        toggle.setAttribute(
            "aria-label",
            show ? "Hide password" : "Show password",
        );
        toggle.setAttribute("aria-pressed", String(show));
        toggle
            .querySelector("[data-eye-slash]")
            ?.toggleAttribute("hidden", !show);
    });
    const form = document.querySelector("[data-login-form]");
    if (!form) return;
    form.noValidate = true;
    function validate(input) {
        let message = "";
        if (input.validity.valueMissing)
            message =
                input.name === "email"
                    ? "Enter your email address."
                    : "Enter your password.";
        else if (input.validity.typeMismatch)
            message = "Enter a valid email address.";
        const error = document.querySelector(`[data-error="${input.name}"]`);
        if (error) error.textContent = message;
        input.setAttribute("aria-invalid", String(Boolean(message)));
        return !message;
    }
    form.addEventListener("submit", (event) => {
        const invalid = [...form.querySelectorAll("input[required]")].filter(
            (input) => !validate(input),
        );
        if (invalid.length) {
            event.preventDefault();
            invalid[0].focus();
        }
    });
    form.querySelectorAll("input[required]").forEach((input) => {
        input.addEventListener("blur", () => validate(input));
        input.addEventListener("input", () => {
            if (input.getAttribute("aria-invalid") === "true") validate(input);
        });
    });
})();
