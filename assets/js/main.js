document.addEventListener('click', (e) => {
  if (e.target.closest('#menu-toggle')) {
    document.getElementById('nav-links').classList.toggle('open');
  }
  const btn = e.target.closest('[data-toggle-password]');
  if (btn) {
    const input = document.getElementById(btn.dataset.togglePassword);
    const show = input.type === 'password';
    input.type = show ? 'text' : 'password';
    btn.textContent = show ? 'Скрыть' : 'Показать';
  }
});

document.addEventListener('input', (e) => {
  const form = e.target.closest('[data-password-match-form]');
  const hint = form?.querySelector('[data-password-hint]');
  const pw2 = form?.querySelector('[data-password-confirm]');
  if (hint && pw2.value) {
    hint.classList.toggle('hidden', form.querySelector('[data-password]').value === pw2.value);
  }
});

document.querySelectorAll('[data-flash]').forEach((el) => setTimeout(() => el.remove(), 4000));
