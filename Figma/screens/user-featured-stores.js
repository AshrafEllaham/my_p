(() => {
  const list = document.getElementById('featuredStoresList');
  if (!list) return;

  const apiOrigin = location.protocol === 'file:'
    ? 'http://127.0.0.1:8000'
    : ['localhost', '127.0.0.1'].includes(location.hostname)
      ? `${location.protocol}//${location.hostname}:8000`
      : location.origin;
  const token = () => localStorage.getItem('saey_api_token');
  const categoryList = document.getElementById('storeMainCategories');
  const governorate = document.getElementById('storeGovernorate');
  const search = document.querySelector('.catalog-search input');
  const loadMore = document.getElementById('loadMoreStores');
  const state = { mainCategoryId: '', governorateId: '', sort: 'high_rate', search: '', page: 1, lastPage: 1, loading: false };

  const pickupCoordinates = () => {
    try {
      const pickup = JSON.parse(localStorage.getItem('saey_pickup_location') || 'null');
      const lat = Number(pickup?.lat);
      const long = Number(pickup?.long);
      return Number.isFinite(lat) && Number.isFinite(long) ? { lat, long } : null;
    } catch {
      return null;
    }
  };

  const request = async (path, authenticated = false) => {
    const response = await fetch(`${apiOrigin}/api/${path}`, {
      headers: {
        Accept: 'application/json',
        'Accept-Language': 'ar',
        ...(authenticated && token() ? { Authorization: `Bearer ${token()}` } : {})
      }
    });
    const payload = await response.json();
    if (!response.ok) {
      const error = new Error(payload.message || 'تعذر تحميل البيانات.');
      error.status = response.status;
      throw error;
    }
    return payload;
  };

  const showState = (message, action = null) => {
    list.replaceChildren();
    const empty = document.createElement('div');
    empty.className = 'catalog-empty-state';
    empty.textContent = message;
    if (action) {
      const control = action.href ? document.createElement('a') : document.createElement('button');
      control.textContent = action.label;
      if (action.href) control.href = action.href;
      else control.addEventListener('click', () => loadStores());
      empty.append(document.createElement('br'), control);
    }
    list.append(empty);
    loadMore.hidden = true;
  };

  const renderStore = store => {
    const card = document.createElement('a');
    card.className = 'catalog-store';
    card.href = `19-store-details.html?store_id=${encodeURIComponent(store.id)}`;

    const logo = document.createElement('span');
    logo.className = 'catalog-store-logo';
    if (store.image || store.cover) {
      const image = document.createElement('img');
      image.src = store.image || store.cover;
      image.alt = `متجر ${store.name || ''}`;
      image.loading = 'lazy';
      logo.replaceChildren(image);
    } else {
      logo.textContent = String(store.name || 'س').trim().charAt(0) || 'س';
    }

    const copy = document.createElement('span');
    copy.className = 'catalog-store-copy';
    const name = document.createElement('h2');
    name.textContent = store.name || 'متجر سعي';
    const category = document.createElement('p');
    category.textContent = store.category_name || 'متجر';
    const meta = document.createElement('span');
    meta.className = 'catalog-store-meta';
    const marker = document.createElement('i');
    const ratingText = store.average_rating === null
      ? 'لا توجد تقييمات'
      : `${Number(store.average_rating).toLocaleString('ar-EG', { maximumFractionDigits: 1 })} ★`;
    const locationText = store.distance_km === null || store.distance_km === undefined
      ? `${ratingText} · ${Number(store.reviews_count || 0).toLocaleString('ar-EG')} تقييم`
      : `${Number(store.distance_km).toLocaleString('ar-EG', { maximumFractionDigits: 1 })} كم · ${ratingText}`;
    meta.append(marker, document.createTextNode(locationText));
    copy.append(name, category, meta);

    const arrow = document.createElement('span');
    arrow.className = 'catalog-store-arrow';
    arrow.textContent = '‹';
    card.append(logo, copy, arrow);
    return card;
  };

  const selectedValue = selector => document.querySelector(`${selector}.selected`)?.dataset.sort || '';

  const loadStores = async (append = false) => {
    if (state.loading) return;
    const coordinates = pickupCoordinates();
    if (state.sort === 'nearest' && !coordinates) {
      showState('احفظ موقع الاستلام أولًا لعرض المتاجر الأقرب إليك.', { href: 'account-pickup.html', label: 'تحديد موقع الاستلام' });
      return;
    }

    state.loading = true;
    loadMore.disabled = true;
    if (!append) showState('جارٍ تحميل المتاجر...');
    const query = new URLSearchParams({ pagination: 'on', limit_per_page: '10', page: String(state.page) });
    query.set('sort', state.sort);
    if (state.mainCategoryId) query.set('main_category_id', state.mainCategoryId);
    if (state.governorateId) query.set('governorate_id', state.governorateId);
    if (state.search) query.set('search', state.search);
    if (coordinates) {
      query.set('lat', String(coordinates.lat));
      query.set('long', String(coordinates.long));
    }

    try {
      const payload = await request(`user/stores/nearby?${query}`, true);
      if (!append) list.replaceChildren();
      if (!payload.data?.length && !append) {
        showState('لا توجد متاجر تطابق اختياراتك حاليًا.');
        return;
      }
      payload.data.forEach(store => list.append(renderStore(store)));
      state.lastPage = Number(payload.last_page || 1);
      loadMore.hidden = state.page >= state.lastPage;
      const subtitle = document.querySelector('.catalog-subtitle');
      subtitle.textContent = `${Number(payload.total || 0).toLocaleString('ar-EG')} متجر`;
    } catch (error) {
      if (error.status === 401) {
        showState('سجّل الدخول لعرض المتاجر.', { href: '01-login.html', label: 'تسجيل الدخول' });
      } else {
        showState(error.message || 'تعذر تحميل المتاجر.', { label: 'إعادة المحاولة' });
      }
    } finally {
      state.loading = false;
      loadMore.disabled = false;
    }
  };

  const loadFilters = async () => {
    try {
      const [categories, governorates] = await Promise.all([
        request('main-categories?pagination=off'),
        request('governorates?pagination=off')
      ]);
      categories.data?.forEach(category => {
        const button = document.createElement('button');
        button.className = 'filter-chip';
        button.type = 'button';
        button.dataset.categoryId = String(category.id);
        button.textContent = category.name;
        categoryList.append(button);
      });
      governorates.data?.forEach(item => {
        const option = document.createElement('option');
        option.value = String(item.id);
        option.textContent = item.name;
        governorate.append(option);
      });
    } catch {
      // Store loading remains available if lookup lists cannot be reached.
    }
  };

  const sheet = document.getElementById('storeFilterSheet');
  const open = document.querySelector('.open-filters');
  const close = () => {
    sheet.classList.remove('is-open');
    sheet.setAttribute('aria-hidden', 'true');
    open.setAttribute('aria-expanded', 'false');
  };
  open.addEventListener('click', () => {
    sheet.classList.add('is-open');
    sheet.setAttribute('aria-hidden', 'false');
    open.setAttribute('aria-expanded', 'true');
  });
  sheet.addEventListener('click', event => {
    if (event.target === sheet || event.target.closest('.close-sheet')) close();
    if (event.target.closest('.apply-filters')) {
      state.page = 1;
      state.mainCategoryId = document.querySelector('.filter-chip.selected')?.dataset.categoryId || '';
      state.governorateId = governorate.value;
      state.sort = selectedValue('.sort-option') || 'high_rate';
      close();
      loadStores();
    }
  });
  categoryList.addEventListener('click', event => {
    const selected = event.target.closest('.filter-chip');
    if (!selected) return;
    categoryList.querySelectorAll('.filter-chip').forEach(button => button.classList.toggle('selected', button === selected));
  });
  document.querySelector('.sort-options').addEventListener('click', event => {
    const selected = event.target.closest('.sort-option');
    if (!selected) return;
    document.querySelectorAll('.sort-option').forEach(button => button.classList.toggle('selected', button === selected));
  });
  loadMore.addEventListener('click', () => {
    state.page += 1;
    loadStores(true);
  });

  let searchTimer;
  search.addEventListener('input', () => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => {
      state.search = search.value.trim();
      state.page = 1;
      loadStores();
    }, 300);
  });

  loadFilters().finally(loadStores);
})();
