document.addEventListener('DOMContentLoaded', () => {
  // Мобильное меню
  const menuBtn = document.getElementById('menu-toggle');
  const mobileMenu = document.getElementById('nav-links');
  if (menuBtn && mobileMenu) {
    menuBtn.addEventListener('click', () => mobileMenu.classList.toggle('open'));
  }

  // Автоскрытие флеш-сообщений
  document.querySelectorAll('[data-flash]').forEach((el) => {
    setTimeout(() => {
      el.style.transition = 'opacity .4s ease';
      el.style.opacity = '0';
      setTimeout(() => el.remove(), 400);
    }, 4000);
  });

  // Показать/скрыть пароль
  document.querySelectorAll('[data-toggle-password]').forEach((btn) => {
    btn.addEventListener('click', () => {
      const input = document.getElementById(btn.dataset.togglePassword);
      if (!input) return;
      const showing = input.type === 'text';
      input.type = showing ? 'password' : 'text';
      btn.textContent = showing ? 'Показать' : 'Скрыть';
    });
  });

  // Модальное окно подтверждения удаления для форм с классом js-confirm-delete
  const modal = document.getElementById('confirm-modal');
  const modalText = document.getElementById('confirm-modal-text');
  const confirmBtn = document.getElementById('confirm-modal-confirm');
  const cancelBtn = document.getElementById('confirm-modal-cancel');
  let pendingForm = null;

  document.querySelectorAll('form.js-confirm-delete').forEach((form) => {
    form.addEventListener('submit', (event) => {
      event.preventDefault();
      pendingForm = form;
      if (modalText) {
        modalText.textContent = form.dataset.confirmMessage || 'Вы уверены, что хотите удалить этот элемент?';
      }
      modal?.classList.remove('hidden');
    });
  });

  cancelBtn?.addEventListener('click', () => {
    modal?.classList.add('hidden');
    pendingForm = null;
  });

  confirmBtn?.addEventListener('click', () => {
    if (pendingForm) {
      pendingForm.submit();
    }
    modal?.classList.add('hidden');
  });

  modal?.addEventListener('click', (e) => {
    if (e.target === modal) {
      modal.classList.add('hidden');
      pendingForm = null;
    }
  });

  // Клиентская валидация совпадения паролей (регистрация / смена пароля)
  const pwForm = document.querySelector('[data-password-match-form]');
  if (pwForm) {
    const pw1 = pwForm.querySelector('[data-password]');
    const pw2 = pwForm.querySelector('[data-password-confirm]');
    const hint = pwForm.querySelector('[data-password-hint]');
    const check = () => {
      if (pw1 && pw2 && pw2.value) {
        const match = pw1.value === pw2.value;
        if (hint) {
          hint.textContent = match ? '' : 'Пароли не совпадают';
          hint.classList.toggle('hidden', match);
        }
      }
    };
    pw1?.addEventListener('input', check);
    pw2?.addEventListener('input', check);
  }
});
