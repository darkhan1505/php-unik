(() => {
  // ---------- AJAX-навигация: ссылки и формы идут через fetch, обновляются только <header> и <main> ----------

  const sameOrigin = (url) => url.origin === location.origin;

  const showError = (message) => {
    const main = document.querySelector('main');
    const box = document.createElement('div');
    box.className = 'container';
    box.innerHTML = '<div data-flash class="flash error"></div>';
    box.firstChild.textContent = message;
    main.prepend(box);
    initFlashes(main);
  };

  const initFlashes = (root) => {
    root.querySelectorAll('[data-flash]').forEach((el) => {
      setTimeout(() => {
        el.style.transition = 'opacity .4s ease';
        el.style.opacity = '0';
        setTimeout(() => el.remove(), 400);
      }, 4000);
    });
  };

  const applyPage = (html, hash) => {
    const doc = new DOMParser().parseFromString(html, 'text/html');
    const newMain = doc.querySelector('main');
    const newHeader = doc.querySelector('header');
    if (!newMain || !newHeader) return false;

    document.title = doc.title;
    document.querySelector('header').replaceWith(newHeader);
    const main = document.querySelector('main');
    main.replaceWith(newMain);

    initFlashes(newMain);
    newMain.querySelector('[autofocus]')?.focus();

    const target = hash ? document.getElementById(decodeURIComponent(hash.slice(1))) : null;
    if (target) {
      target.scrollIntoView();
    } else {
      window.scrollTo(0, 0);
    }
    return true;
  };

  /**
   * Загружает страницу без перезагрузки.
   * push: добавлять ли запись в историю (false для кнопок «назад/вперёд»).
   */
  const navigate = async (url, options = {}, push = true) => {
    const isGet = !options.method || options.method.toUpperCase() === 'GET';
    document.body.classList.add('loading');

    try {
      const res = await fetch(url, { ...options, headers: { 'X-Requested-With': 'fetch' } });
      const type = res.headers.get('content-type') || '';
      if (!type.includes('text/html')) {
        if (isGet) {
          location.href = url;
        } else {
          showError('Не удалось выполнить действие. Попробуйте ещё раз.');
        }
        return;
      }

      const html = await res.text();
      const hash = isGet ? new URL(url, location.href).hash : '';
      if (!applyPage(html, hash)) {
        if (isGet) location.href = url;
        return;
      }

      const finalUrl = res.url + hash;
      if (push) {
        const same = new URL(finalUrl).href === location.href;
        history[same ? 'replaceState' : 'pushState']({ ajax: true }, '', finalUrl);
      }
    } catch (err) {
      if (isGet) {
        location.href = url;
      } else {
        showError('Ошибка сети. Проверьте соединение и повторите.');
      }
    } finally {
      document.body.classList.remove('loading');
    }
  };

  document.addEventListener('click', (event) => {
    if (event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;

    const link = event.target.closest('a[href]');
    if (!link || link.target || link.hasAttribute('download') || link.hasAttribute('data-no-ajax')) return;

    const url = new URL(link.href, location.href);
    if (!sameOrigin(url) || url.pathname.startsWith('/assets/')) return;

    // Якорь на текущей странице — обычная прокрутка
    if (url.pathname === location.pathname && url.search === location.search && url.hash) return;

    event.preventDefault();
    document.getElementById('nav-links')?.classList.remove('open');
    navigate(url.href);
  });

  document.addEventListener('submit', (event) => {
    const form = event.target;
    if (!(form instanceof HTMLFormElement) || event.defaultPrevented || form.hasAttribute('data-no-ajax')) return;

    // Формы с подтверждением удаления сначала показывают модальное окно
    if (form.classList.contains('js-confirm-delete') && form.dataset.confirmed !== '1') {
      event.preventDefault();
      openConfirm(form);
      return;
    }
    delete form.dataset.confirmed;

    const url = new URL(form.getAttribute('action') || location.href, location.href);
    if (!sameOrigin(url)) return;

    event.preventDefault();
    const data = new FormData(form, event.submitter);

    if ((form.method || 'get').toLowerCase() === 'post') {
      navigate(url.href, { method: 'POST', body: new URLSearchParams(data) });
    } else {
      url.search = new URLSearchParams(data).toString();
      navigate(url.href);
    }
  });

  window.addEventListener('popstate', () => navigate(location.href, {}, false));

  // ---------- Интерактивность (делегирование событий, работает после подмены страницы) ----------

  // Мобильное меню
  document.addEventListener('click', (event) => {
    if (event.target.closest('#menu-toggle')) {
      document.getElementById('nav-links')?.classList.toggle('open');
    }
  });

  // Показать/скрыть пароль
  document.addEventListener('click', (event) => {
    const btn = event.target.closest('[data-toggle-password]');
    if (!btn) return;
    const input = document.getElementById(btn.dataset.togglePassword);
    if (!input) return;
    const showing = input.type === 'text';
    input.type = showing ? 'password' : 'text';
    btn.textContent = showing ? 'Показать' : 'Скрыть';
  });

  // Проверка совпадения паролей (регистрация / смена пароля)
  document.addEventListener('input', (event) => {
    const form = event.target.closest('[data-password-match-form]');
    if (!form) return;
    const pw1 = form.querySelector('[data-password]');
    const pw2 = form.querySelector('[data-password-confirm]');
    const hint = form.querySelector('[data-password-hint]');
    if (pw1 && pw2 && pw2.value && hint) {
      hint.classList.toggle('hidden', pw1.value === pw2.value);
    }
  });

  // Модальное окно подтверждения удаления
  const modal = document.getElementById('confirm-modal');
  const modalText = document.getElementById('confirm-modal-text');
  let pendingForm = null;

  const openConfirm = (form) => {
    pendingForm = form;
    if (modalText) {
      modalText.textContent = form.dataset.confirmMessage || 'Вы уверены, что хотите удалить этот элемент?';
    }
    modal?.classList.remove('hidden');
  };

  const closeConfirm = () => {
    modal?.classList.add('hidden');
    pendingForm = null;
  };

  document.getElementById('confirm-modal-cancel')?.addEventListener('click', closeConfirm);
  modal?.addEventListener('click', (e) => {
    if (e.target === modal) closeConfirm();
  });
  document.getElementById('confirm-modal-confirm')?.addEventListener('click', () => {
    const form = pendingForm;
    closeConfirm();
    if (form) {
      form.dataset.confirmed = '1';
      form.requestSubmit(); // снова вызовет обработчик submit, который отправит форму через fetch
    }
  });

  initFlashes(document);
})();
