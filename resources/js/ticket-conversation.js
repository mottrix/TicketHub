document.querySelectorAll("[data-ticket-conversation]").forEach((conversation) => {
    const stateSelect = conversation.querySelector("[data-conversation-state]");
    const scrollArea = conversation.querySelector("[data-conversation-scroll]");
    const composer = conversation.querySelector("[data-composer]");
    const reply = conversation.querySelector("[data-reply]");
    const feedback = conversation.querySelector("[data-reply-feedback]");
    const ticketResolved = conversation.dataset.ticketResolved === "true";

    conversation.querySelector("[data-preview-controls]").hidden = false;

    const updateState = () => {
        const state = stateSelect.value;
        const resolved = ticketResolved || state === "resolved";
        const visiblePanel = ["normal", "resolved"].includes(state) ? "messages" : state;

        conversation.querySelectorAll("[data-conversation-panel]").forEach((panel) => {
            panel.hidden = panel.dataset.conversationPanel !== visiblePanel;
        });

        composer.disabled = resolved || state === "loading";
        conversation.querySelector("[data-resolved-notice]").classList.toggle("hidden", !resolved);
        scrollArea.setAttribute("aria-busy", String(state === "loading"));
        scrollArea.scrollTop = 0;
        reply.placeholder = resolved
            ? "This ticket is resolved."
            : state === "loading" ? "Loading messages…" : "Write your reply...";
        feedback.textContent = state === "loading" ? "Preview: loading messages." : "";
    };

    stateSelect.addEventListener("change", updateState);
    updateState();
});
