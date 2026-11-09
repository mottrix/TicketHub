(() => {
    "use strict";

    const form = document.getElementById("ticketForm");
    if (!form) return;

    const title = document.getElementById("title");
    const description = document.getElementById("description");
    const descriptionInput = document.getElementById("descriptionInput");
    const priorityInput = document.getElementById("priorityInput");
    const statusInput = document.getElementById("statusInput");

    const template = document.createElement("template");
    template.innerHTML = descriptionInput.value;
    const allowed = new Set(["B", "STRONG", "I", "EM", "UL", "OL", "LI", "P", "BR", "DIV", "A"]);

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

    for (const [attribute, input] of [["data-priority", priorityInput], ["data-status", statusInput]]) {
        select(attribute, input);
        document.querySelectorAll("[" + attribute + "]").forEach((button) => {
            button.addEventListener("click", () => {
                input.value = button.getAttribute(attribute);
                select(attribute, input);
            });
        });
    }

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
