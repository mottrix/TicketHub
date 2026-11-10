/* Shared create/edit controller. The form's action and method come from Blade;
   this script only prepares fields and provides editor interactions. */
(() => {
    "use strict";

    const form = document.getElementById("ticketForm");
    if (!form) return;

    const title = document.getElementById("title");
    const description = document.getElementById("description");
    const descriptionInput = document.getElementById("descriptionInput");
    const priorityInput = document.getElementById("priorityInput");
    const statusInput = document.getElementById("statusInput");

    /* old() or the saved description arrives as escaped text in a hidden input.
       Parse it off-screen, then keep only supported formatting before displaying it. */
    const template = document.createElement("template");
    template.innerHTML = descriptionInput.value;
    const allowed = new Set(["B", "STRONG", "I", "EM", "UL", "OL", "LI", "P", "BR", "DIV", "A"]);

    /* Strip unsupported elements and attributes, allowing only HTTP(S) link hrefs.
       This runs on initial content and again before submission; the server also validates input. */
    function clean(node) {
        for (const child of [...node.childNodes]) {
            if (child.nodeType === Node.ELEMENT_NODE) {
                if (!allowed.has(child.tagName)) {
                    child.replaceWith(document.createTextNode(child.textContent || ""));
                    continue;
                }
                for (const attribute of [...child.attributes]) {
                    if (!(child.tagName === "A" && attribute.name === "href" && /^https?:\/\//i.test(attribute.value))) {
                        child.removeAttribute(attribute.name);
                    }
                }
                clean(child);
            } else if (child.nodeType !== Node.TEXT_NODE) {
                child.remove();
            }
        }
    }

    clean(template.content);
    description.replaceChildren(template.content);

    function select(attribute, input) {
        if (!input) return;
        document.querySelectorAll("[" + attribute + "]").forEach((button) => {
            const active = button.getAttribute(attribute) === input.value;
            button.classList.toggle("selected", active);
            button.setAttribute("aria-pressed", String(active));
        });
    }

    /* These buttons are not form fields: copy selections into named hidden inputs.
       Creation and non-admin editing omit status, so that group is optional. */
    for (const [attribute, input] of [["data-priority", priorityInput], ["data-status", statusInput]]) {
        select(attribute, input);
        document.querySelectorAll("[" + attribute + "]").forEach((button) => {
            button.addEventListener("click", () => {
                input.value = button.getAttribute(attribute);
                select(attribute, input);
            });
        });
    }

    /* Toolbar data attributes map to browser formatting commands for the active editor. */
    document.querySelectorAll("[data-command]").forEach((button) => {
        button.addEventListener("click", () => {
            description.focus();
            document.execCommand(button.dataset.command, false, null);
        });
    });

    document.getElementById("linkTool").addEventListener("click", () => {
        const value = prompt("Enter link URL (https://…)");
        if (!value) return;
        try {
            const url = new URL(value);
            if (!["https:", "http:"].includes(url.protocol)) return;
            description.focus();
            document.execCommand("createLink", false, url.href);
        } catch {
            alert("Please enter a valid http or https URL.");
        }
    });

    title.addEventListener("input", () => document.getElementById("titleField").classList.remove("invalid"));
    description.addEventListener("input", () => document.getElementById("descriptionField").classList.remove("invalid"));

    document.getElementById("cancel").addEventListener("click", () => {
        window.location.assign(form.dataset.cancelUrl);
    });

    /* contenteditable elements are not submitted by HTML forms. After checking visible
       text, copy the cleaned HTML into the description field and allow the normal request. */
    form.addEventListener("submit", (event) => {
        const missingTitle = !title.value.trim();
        const missingDescription = !description.textContent.trim();
        document.getElementById("titleField").classList.toggle("invalid", missingTitle);
        document.getElementById("descriptionField").classList.toggle("invalid", missingDescription);

        if (missingTitle || missingDescription) {
            event.preventDefault();
            (missingTitle ? title : description).focus();
            return;
        }

        clean(description);
        descriptionInput.value = description.innerHTML;
    });
})();
