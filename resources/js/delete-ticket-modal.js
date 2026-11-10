/* Standalone modal preview: opening locks page scrolling and closing restores focus.
   The delete button only shows a toast here; actual ticket deletion uses Blade forms. */
(() => {
      'use strict';
      const overlay = document.getElementById('overlay');
      const modal = document.getElementById('modal');
      const cancelBtn = document.getElementById('cancelBtn');
      const deleteBtn = document.getElementById('deleteBtn');
      const toast = document.getElementById('toast');
      if (!overlay || !modal || !cancelBtn || !deleteBtn || !toast) return;
      let lastFocused = document.activeElement;

      function closeModal(showToast = false) {
        overlay.classList.add('hidden');
        document.body.style.overflow = '';
        if (showToast) {
          toast.classList.add('show');
          window.setTimeout(() => toast.classList.remove('show'), 2600);
        }
        if (lastFocused && typeof lastFocused.focus === 'function') lastFocused.focus();
      }

      function openModal() {
        overlay.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        lastFocused = document.activeElement;
        cancelBtn.focus();
      }

      /* Cycle Tab between the dialog's two buttons while the overlay is open. */
      function trapFocus(event) {
        if (event.key !== 'Tab') return;
        const focusable = [cancelBtn, deleteBtn];
        const first = focusable[0];
        const last = focusable[focusable.length - 1];
        if (event.shiftKey && document.activeElement === first) {
          event.preventDefault();
          last.focus();
        } else if (!event.shiftKey && document.activeElement === last) {
          event.preventDefault();
          first.focus();
        }
      }

      cancelBtn.addEventListener('click', () => closeModal(false));
      deleteBtn.addEventListener('click', () => closeModal(true));
      overlay.addEventListener('click', (event) => {
        if (event.target === overlay) closeModal(false);
      });
      document.addEventListener('keydown', (event) => {
        if (overlay.classList.contains('hidden')) return;
        if (event.key === 'Escape') closeModal(false);
        trapFocus(event);
      });

      openModal();
    })();
