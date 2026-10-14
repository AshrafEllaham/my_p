(function () {
  const apiBaseUrl = 'http://127.0.0.1:8000/api';
  const pageSize = 10;
  const token = () => localStorage.getItem('saey_api_token');
  const escapeHtml = value => String(value ?? '').replace(/[&<>"']/g, character => ({
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
    '"': '&quot;',
    "'": '&#039;',
  })[character]);

  const request = async (path, options = {}) => {
    const accessToken = token();
    if (!accessToken) {
      const error = new Error('سجل الدخول لعرض المفضلة.');
      error.status = 401;
      throw error;
    }

    let response;
    try {
      response = await fetch(apiBaseUrl + path, {
        ...options,
        headers: {
          Accept: 'application/json',
          'Accept-Language': document.documentElement.lang || 'ar',
          Authorization: `Bearer ${accessToken}`,
          ...(options.headers || {}),
        },
      });
    } catch (cause) {
      const error = new Error('تعذر الاتصال بالخادم. تحقق من تشغيل واجهة API ثم أعد المحاولة.');
      error.cause = cause;
      throw error;
    }

    const result = await response.json().catch(() => ({}));
    if (!response.ok) {
      const error = new Error(result.message || 'تعذر تحميل المفضلة.');
      error.status = response.status;
      throw error;
    }

    return result;
  };

  const init = () => {
    const grid = document.getElementById('favoriteItems');
    if (!grid) return;

    const tabs = [...document.querySelectorAll('.favorites-tabs button[data-type]')];
    const empty = document.getElementById('favoritesEmpty');
    const message = document.getElementById('favoritesMessage');
    const loadMore = document.getElementById('favoritesLoadMore');
    const count = document.getElementById('favoritesCount');
    let activeType = 'product';
    let currentPage = 1;
    let lastPage = 1;
    let requestVersion = 0;
    let rows = [];

    const render = () => {
      grid.innerHTML = rows.map(favorite => {
        const item = favorite.item || {};
        const href = favorite.type === 'product'
          ? `12-product-details.html?product_id=${encodeURIComponent(item.id || favorite.favoriteable_id)}`
          : `19-store-details.html?store_id=${encodeURIComponent(item.id || favorite.favoriteable_id)}`;
        const image = item.image || item.cover || '';
        const name = escapeHtml(item.name || 'عنصر محفوظ');
        const imageMarkup = image
          ? `<img src="${escapeHtml(image)}" alt="${name}" loading="lazy">`
          : '<span class="favorite-image-fallback" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1.1-1.1a5.5 5.5 0 0 0-7.8 7.8L12 21l8.9-8.6a5.5 5.5 0 0 0-.1-7.8Z"/></svg></span>';

        if (favorite.type === 'store') {
          const details = [item.category_name, item.average_rating ? `التقييم ${Number(item.average_rating).toFixed(1)}` : null]
            .filter(Boolean).map(escapeHtml).join(' · ');
          return `<article class="favorite-store">
            <button class="favorite-remove" type="button" data-type="store" data-id="${Number(favorite.favoriteable_id)}" aria-label="إزالة ${name} من المفضلة"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m6 6 12 12M18 6 6 18"/></svg></button>
            <a class="favorite-open" href="${href}">${imageMarkup}<small>${details || 'متجر محفوظ'}</small><strong>${name}</strong></a>
          </article>`;
        }

        const details = escapeHtml(item.category_name || item.store_name || 'منتج محفوظ');
        const price = item.price == null ? '' : `<b>${Number(item.price).toLocaleString('ar-EG')} ج.م</b>`;
        return `<article class="favorite-product">
          <button class="favorite-remove" type="button" data-type="product" data-id="${Number(favorite.favoriteable_id)}" aria-label="إزالة ${name} من المفضلة"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m6 6 12 12M18 6 6 18"/></svg></button>
          <a class="favorite-open" href="${href}">${imageMarkup}<small>${details}</small><strong>${name}</strong>${price}</a>
        </article>`;
      }).join('');

      const isEmpty = rows.length === 0;
      empty.hidden = !isEmpty;
      grid.hidden = isEmpty;
      loadMore.hidden = isEmpty || currentPage >= lastPage;
      count.textContent = rows.length ? `${rows.length.toLocaleString('ar-EG')} عنصر` : '';
    };

    const showError = (error, retry, errorTitle = 'تعذر تحميل المفضلة') => {
      message.hidden = false;
      message.innerHTML = `<strong>${error.status === 401 ? 'يلزم تسجيل الدخول' : errorTitle}</strong><span>${escapeHtml(error.message)}</span><button type="button" id="favoritesRetry">إعادة المحاولة</button>`;
      document.getElementById('favoritesRetry').onclick = retry;
    };

    const loadPage = async (page, reset = true) => {
      const version = ++requestVersion;
      message.hidden = true;
      if (reset) {
        currentPage = 1;
        lastPage = 1;
        rows = [];
        empty.hidden = true;
        grid.hidden = false;
        loadMore.hidden = true;
        loadMore.disabled = true;
        SaeyUX.loading(grid, 2);
      } else {
        loadMore.disabled = true;
        loadMore.textContent = 'جارٍ التحميل…';
      }

      const query = new URLSearchParams({
        type: activeType,
        pagination: 'on',
        limit_per_page: String(pageSize),
        page: String(page),
      });

      try {
        const result = await request(`/user/favorites?${query}`);
        if (version !== requestVersion) return;
        const nextRows = Array.isArray(result.data) ? result.data : [];
        rows = reset ? nextRows : rows.concat(nextRows);
        currentPage = Number(result.current_page || page);
        lastPage = Number(result.last_page || 1);
        render();
      } catch (error) {
        if (version !== requestVersion) return;
        if (reset) grid.innerHTML = '';
        showError(error, () => loadPage(page, reset));
      } finally {
        if (version === requestVersion) {
          loadMore.disabled = false;
          loadMore.textContent = 'تحميل المزيد';
        }
      }
    };

    tabs.forEach(tab => {
      tab.addEventListener('click', () => {
        if (tab.dataset.type === activeType) return;
        activeType = tab.dataset.type;
        tabs.forEach(item => {
          item.classList.toggle('active', item === tab);
          item.setAttribute('aria-selected', String(item === tab));
        });
        loadPage(1);
      });
    });

    grid.addEventListener('click', async event => {
      const button = event.target.closest('.favorite-remove');
      if (!button) return;
      event.preventDefault();
      event.stopPropagation();
      button.disabled = true;

      try {
        await request('/user/favorites/toggle', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ type: button.dataset.type, favoriteable_id: Number(button.dataset.id) }),
        });
        rows = rows.filter(item => !(item.type === button.dataset.type && Number(item.favoriteable_id) === Number(button.dataset.id)));
        message.hidden = true;
        render();
      } catch (error) {
        button.disabled = false;
        showError(error, () => loadPage(1), 'تعذر تحديث المفضلة');
      }
    });

    loadMore.addEventListener('click', () => loadPage(currentPage + 1, false));
    loadPage(1);
  };

  document.addEventListener('DOMContentLoaded', init);
})();
