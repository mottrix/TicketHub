(() => {
  const form = document.querySelector('[data-register-form]');
  if (!form) return;
  const fields = [...form.querySelectorAll('input[required]')];
  const password = form.elements.password;
  const confirmation = form.elements.password_confirmation;
  const toggle = form.querySelector('[data-confirm-toggle]');
  toggle?.addEventListener('click', () => {
    const show = confirmation.type === 'password';
    confirmation.type = show ? 'text' : 'password';
    toggle.setAttribute('aria-label', show ? 'Hide confirm password' : 'Show confirm password');
    toggle.setAttribute('aria-pressed', String(show));
    toggle.querySelector('[data-eye-slash]')?.toggleAttribute('hidden', !show);
  });
  const empty = {name:'Enter your full name.',email:'Enter your email address.',password:'Enter your password.',password_confirmation:'Confirm your password.'};
  function validate(input) {
    let message = '';
    if (input.validity.valueMissing || (input.name === 'name' && !input.value.trim())) message = empty[input.name];
    else if (input.validity.typeMismatch) message = 'Enter a valid email address.';
    else if (input === confirmation && input.value !== password.value) message = 'Your passwords do not match.';
    input.setAttribute('aria-invalid', String(Boolean(message)));
    form.querySelector(`[data-error="${input.name}"]`).textContent = message;
    return !message;
  }
  form.noValidate = true;
  fields.forEach(input => {
    input.addEventListener('blur', () => validate(input));
    input.addEventListener('input', () => {
      if (input.getAttribute('aria-invalid') === 'true') validate(input);
      if (input === password && confirmation.value) validate(confirmation);
    });
  });
  form.addEventListener('submit', event => {
    const invalid = fields.filter(input => !validate(input));
    if (invalid.length) { event.preventDefault(); invalid[0].focus(); }
  });
})();
