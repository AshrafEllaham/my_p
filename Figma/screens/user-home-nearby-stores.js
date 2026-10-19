(() => {
  const list = document.getElementById('nearbyStores');
  if (!list) return;

  const apiOrigin = location.protocol === 'file:'
    ? 'http://127.0.0.1:8000'
    : ['localhost', '127.0.0.1'].includes(location.hostname)
      ? `${location.protocol}//${location.hostname}:8000`
      : location.origin;
  const token = () => localStorage.getItem('saey_api_token');
  const locationIcon = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 10c0 5-8 11-8 11S4 15 4 10a8 8 0 1 1 16 0Z"/><circle cx="12" cy="10" r="2.5"/></svg>';

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

  const showState = (message, action = null) => {
    list.replaceChildren();
    const state = document.createElement('div');
    state.className = 'nearby-stores-state';
    const text = document.createElement('span');
    text.textContent = message;
    state.append(text);
    if (action) {
      const control = action.href ? document.createElement('a') : document.createElement('button');
      control.className = 'recommended-products-action';
      control.textContent = action.label;
      if (action.href) control.href = action.href;
      else {
        control.type = 'button';
        control.addEventListener('click', load);
      }
      state.append(control);
    }
    list.append(state);
  };

  const storeCard = store => {
    const card = document.createElement('a');
    card.className = 'nearby-store-card';
    card.href = `19-store-details.html?store_id=${encodeURIComponent(store.id)}`;

    const mediaUrl = store.cover || store.image;
    if (mediaUrl) {
      const image = document.createElement('img');
      image.src = mediaUrl;
      image.alt = `متجر ${store.name || ''}`;
      image.loading = 'lazy';
      card.append(image);
    } else {
      const placeholder = document.createElement('span');
      placeholder.className = 'nearby-store-placeholder';
      placeholder.textContent = String(store.name || 'س').trim().charAt(0) || 'س';
      card.append(placeholder);
    }

    const copy = document.createElement('span');
    copy.className = 'nearby-store-copy';
    const title = document.createElement('span');
    title.className = 'store-title';
    const name = document.createElement('strong');
    name.textContent = store.name || 'متجر سعي';
    title.append(name);
    const category = document.createElement('small');
    category.textContent = store.category_name || 'متجر';
    const meta = document.createElement('span');
    meta.className = 'store-meta';
    const rating = document.createElement('b');
    rating.textContent = `${Number(store.average_rating || 0).toLocaleString('ar-EG', { maximumFractionDigits: 1 })} ★`;
    const location = document.createElement('em');
    if (store.distance_km !== null && store.distance_km !== undefined) {
      location.innerHTML = locationIcon;
      location.append(document.createTextNode(`${Number(store.distance_km).toLocaleString('ar-EG', { maximumFractionDigits: 1 })} كم`));
    } else {
      location.textContent = `${Number(store.reviews_count || 0).toLocaleString('ar-EG')} تقييم`;
    }
    meta.append(rating, location);
    copy.append(title, category, meta);
    card.append(copy);

    return card;
  };

  async function load() {
    showState('جارٍ تحميل المتاجر...');
    const query = new URLSearchParams({ limit_per_page: '6' });
    const browserCoordinates = pickupCoordinates();
    let coordinates = null;
    if (token()) {
      try {
        const profileResponse = await fetch(`${apiOrigin}/api/user/profile`, {
          headers: { Accept: 'application/json', 'Accept-Language': 'ar', Authorization: `Bearer ${token()}` }
        });
        if (profileResponse.ok) {
          const profilePayload = await profileResponse.json();
          const profile = profilePayload.data || {};
          const lat = Number(profile.pickup_latitude);
          const long = Number(profile.pickup_longitude);
          if (Number.isFinite(lat) && Number.isFinite(long)) coordinates = { lat, long };
          if (profile.governorate_id) query.set('governorate_id', String(profile.governorate_id));
        }
      } catch {
        // Saved browser coordinates can still be used when the profile request fails.
      }
    }
    coordinates ||= browserCoordinates;
    if (coordinates) {
      query.set('lat', String(coordinates.lat));
      query.set('long', String(coordinates.long));
      query.set('sort', 'nearest');
    }

    try {
      const response = await fetch(`${apiOrigin}/api/user/stores/nearby?${query}`, {
        headers: {
          Accept: 'application/json',
          'Accept-Language': 'ar',
          ...(token() ? { Authorization: `Bearer ${token()}` } : {})
        }
      });
      const payload = await response.json();
      if (response.status === 401) {
        showState('سجّل الدخول لعرض المتاجر القريبة منك.', { label: 'تسجيل الدخول', href: '01-login.html' });
        return;
      }
      if (!response.ok) throw new Error(payload.message || 'تعذر تحميل المتاجر القريبة.');
      list.replaceChildren();
      if (!payload.data?.length) {
        showState(coordinates ? 'لا توجد متاجر قريبة متاحة حاليًا.' : 'لا توجد متاجر متاحة حاليًا.');
        return;
      }
      payload.data.forEach(store => list.append(storeCard(store)));
    } catch (error) {
      showState(error.message || 'تعذر تحميل المتاجر القريبة.', { label: 'إعادة المحاولة' });
    }
  }

  load();
})();
