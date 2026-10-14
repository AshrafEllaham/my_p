(() => {
  let storeId = window.saeyStoreId;
  const state = document.getElementById('storeDetailsState');
  const profile = document.getElementById('storeProfile');
  const productList = document.getElementById('storeProducts');
  const productsState = document.getElementById('storeProductsState');
  const productSearch = document.getElementById('storeProductSearch');
  const productSort = document.getElementById('storeProductSort');
  const loadMoreProducts = document.getElementById('loadMoreProducts');
  const adsSection = document.getElementById('storeAdsSection');
  const adList = document.getElementById('storeAds');
  const adsState = document.getElementById('storeAdsState');
  const loadMoreAds = document.getElementById('loadMoreStoreAds');
  let productsPage = 1;
  let productsLastPage = 1;
  let productsRequestId = 0;
  let productSearchTimer;
  let adsPage = 1;
  let adsLastPage = 1;
  let adsRequestId = 0;
  const token = () => localStorage.getItem('saey_api_token');
  const apiOrigin = location.protocol === 'file:'
    ? 'http://127.0.0.1:8000'
    : ['localhost', '127.0.0.1'].includes(location.hostname)
      ? `${location.protocol}//${location.hostname}:8000`
      : location.origin;

  const setState = (message, login = false) => {
    state.replaceChildren(document.createTextNode(message));
    if (login) {
      const link = document.createElement('a');
      link.href = '01-login.html';
      link.textContent = 'تسجيل الدخول';
      state.append(document.createElement('br'), link);
    }
    state.hidden = false;
  };

  const setProductsState = (message, login = false) => {
    productsState.replaceChildren(document.createTextNode(message));
    if (login) {
      const link = document.createElement('a');
      link.href = '01-login.html';
      link.textContent = 'تسجيل الدخول';
      productsState.append(document.createElement('br'), link);
    }
    productsState.hidden = false;
  };

  const setAdsState = (message, variant = 'empty') => {
    const icon = document.createElement('span');
    icon.className = 'store-ads-state-icon';
    icon.innerHTML = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m4 9 16-5v16L4 15Z"/><path d="M4 9v6m3 2 1 4h4l-2-5"/></svg>';
    const content = document.createElement('span');
    content.className = 'store-ads-state-copy';
    const title = document.createElement('strong');
    title.textContent = message;
    const description = document.createElement('small');
    description.textContent = ({
      loading: 'جارٍ تحديث قائمة الإعلانات المتاحة.',
      empty: 'ستظهر هنا الحملات المتاحة لهذا المتجر عند وجودها.',
      auth: 'سجّل الدخول مرة أخرى لمتابعة عرض الإعلانات.',
      error: 'تحقق من الاتصال ثم حاول تحميل الإعلانات مرة أخرى.',
    }[variant] || '');
    content.append(title, description);
    adsState.className = `store-ads-state is-${variant}`;
    adsState.replaceChildren(icon, content);
    if (variant === 'auth') {
      const login = document.createElement('a');
      login.className = 'store-ads-state-action';
      login.href = '01-login.html';
      login.textContent = 'تسجيل الدخول';
      adsState.append(login);
    } else if (variant === 'error') {
      const retry = document.createElement('button');
      retry.className = 'store-ads-state-action';
      retry.type = 'button';
      retry.textContent = 'إعادة المحاولة';
      retry.addEventListener('click', () => loadStoreActiveAds());
      adsState.append(retry);
    }
    adsState.hidden = false;
  };

  const formatNumber = value => Number(value || 0).toLocaleString('ar-EG', { maximumFractionDigits: 1 });

  const renderPolicy = policy => {
    const section = document.getElementById('storePolicySection');
    const container = document.getElementById('storePolicies');
    container.replaceChildren();
    if (!policy) {
      section.hidden = true;
      return;
    }

    const rows = [];
    if (policy.returns_enabled) {
      rows.push({
        title: `الاسترجاع خلال ${formatNumber(policy.return_window_days)} يومًا`,
        description: policy.return_terms || 'راجع المتجر لمعرفة الشروط والتفاصيل.',
        icon: '<path d="M9 7H4v5"/><path d="M4 12a8 8 0 1 0 2-5M4 12l5-5"/>'
      });
    }
    if (policy.exchanges_enabled) {
      rows.push({
        title: 'الاستبدال متاح',
        description: policy.exchange_terms || 'راجع المتجر لمعرفة الشروط والتفاصيل.',
        icon: '<path d="m7 7 3-3 3 3"/><path d="M10 4v11a4 4 0 0 0 4 4h3"/><path d="m17 16 3 3-3 3"/>'
      });
    }
    if (policy.cancellation_enabled) {
      rows.push({
        title: 'إلغاء الطلب متاح',
        description: policy.cancellation_terms || 'راجع المتجر لمعرفة الشروط والتفاصيل.',
        icon: '<path d="M12 8v4l3 2"/><circle cx="12" cy="12" r="9"/>'
      });
    }
    if (policy.pickup_hold_hours) {
      rows.push({
        title: `حجز الاستلام لمدة ${formatNumber(policy.pickup_hold_hours)} ساعة`,
        description: policy.pickup_instructions || 'يمكنك استلام الطلب من المتجر خلال مدة الحجز.',
        icon: '<path d="M4 10v10h16V10M3 10l2-6h14l2 6"/>'
      });
    }
    rows.forEach(row => {
      const element = document.createElement('div');
      element.className = 'policy-row';
      const icon = document.createElement('span');
      icon.innerHTML = `<svg viewBox="0 0 24 24">${row.icon}</svg>`;
      const copy = document.createElement('div');
      const title = document.createElement('strong');
      title.textContent = row.title;
      const description = document.createElement('small');
      description.textContent = row.description;
      copy.append(title, description);
      element.append(icon, copy);
      container.append(element);
    });
    section.hidden = rows.length === 0;
  };

  const renderProducts = (products, append = false) => {
    if (!append) productList.replaceChildren();
    if (!products?.length) {
      if (!append) setProductsState('لا توجد منتجات مطابقة في هذا المتجر حاليًا.');
      return;
    }
    products.forEach(product => {
      const card = document.createElement('a');
      card.className = 'store-product-card';
      card.href = `12-product-details.html?product_id=${encodeURIComponent(product.id)}`;
      const imageWrap = document.createElement('span');
      imageWrap.className = 'store-product-image';
      if (product.image) {
        const image = document.createElement('img');
        image.src = product.image;
        image.alt = product.name || '';
        image.loading = 'lazy';
        imageWrap.append(image);
      } else {
        imageWrap.textContent = String(product.name || 'س').trim().charAt(0) || 'س';
      }
      if (Number(product.discount_percentage) > 0) {
        const discount = document.createElement('em');
        discount.textContent = `خصم ${formatNumber(product.discount_percentage)}٪`;
        imageWrap.append(discount);
      }
      const name = document.createElement('strong');
      name.textContent = product.name || 'منتج';
      const price = document.createElement('small');
      price.textContent = `${formatNumber(product.price)} ج.م`;
      card.append(imageWrap, name, price);
      productList.append(card);
    });
    productsState.hidden = true;
  };

  const loadStoreProducts = async (append = false) => {
    if (!storeId) return;

    const requestId = ++productsRequestId;
    const requestedPage = append ? productsPage + 1 : 1;
    if (!append) {
      productsPage = 1;
      productsLastPage = 1;
      productList.replaceChildren();
      loadMoreProducts.hidden = true;
      setProductsState('جارٍ تحميل منتجات المتجر...');
    } else {
      loadMoreProducts.disabled = true;
    }

    const query = new URLSearchParams({
      pagination: 'on',
      limit_per_page: '6',
      page: String(requestedPage),
      sort: productSort.value,
    });
    if (productSearch.value.trim()) query.set('search', productSearch.value.trim());

    try {
      const response = await fetch(`${apiOrigin}/api/user/stores/${encodeURIComponent(storeId)}/products?${query}`, {
        headers: {
          Accept: 'application/json',
          'Accept-Language': 'ar',
          Authorization: `Bearer ${token()}`
        }
      });
      const payload = await response.json();
      if (requestId !== productsRequestId) return;
      if (response.status === 401) {
        setProductsState('انتهت جلسة تسجيل الدخول. سجّل الدخول مرة أخرى لعرض المنتجات.', true);
        return;
      }
      if (!response.ok) throw new Error(payload.message || 'تعذر تحميل منتجات المتجر.');

      renderProducts(payload.data, append);
      productsPage = Number(payload.current_page || requestedPage);
      productsLastPage = Number(payload.last_page || productsPage);
      loadMoreProducts.hidden = productsPage >= productsLastPage;
    } catch (error) {
      if (requestId === productsRequestId) {
        setProductsState(error.message || 'تعذر تحميل منتجات المتجر.');
      }
    } finally {
      loadMoreProducts.disabled = false;
    }
  };

  const adActionUrl = ad => {
    if (['view_product', 'buy_now'].includes(ad.action) && ad.product_id) {
      return `12-product-details.html?product_id=${encodeURIComponent(ad.product_id)}`;
    }
    if (ad.action === 'start_chat') {
      const store = window.saeyStoreDetails;
      return `14-chat-conversation.html?store=${encodeURIComponent(store?.name || 'المتجر')}&store_id=${encodeURIComponent(store?.owner_id || '')}`;
    }

    return `19-store-details.html?store_id=${encodeURIComponent(storeId)}`;
  };

  const renderStoreAds = (ads, append = false) => {
    if (!append) adList.replaceChildren();
    if (!ads?.length) {
      if (!append) setAdsState('لا توجد إعلانات نشطة لهذا المتجر حاليًا.', 'empty');
      return;
    }

    ads.forEach(ad => {
      const card = document.createElement('article');
      card.className = 'youtube-ad compact store-active-ad';

      const media = document.createElement('div');
      media.className = 'youtube-ad-media';
      if (ad.media_type === 'video' && ad.media_path) {
        const video = document.createElement('video');
        video.src = ad.media_path;
        video.controls = true;
        video.playsInline = true;
        video.preload = 'metadata';
        video.setAttribute('aria-label', ad.title || 'فيديو الإعلان');
        media.append(video);
      } else if (ad.media_type === 'image' && ad.media_path) {
        const image = document.createElement('img');
        image.src = ad.media_path;
        image.alt = ad.title || 'صورة الإعلان';
        image.loading = 'lazy';
        media.append(image);
      } else {
        const fileLink = document.createElement('a');
        fileLink.href = ad.media_path || '#';
        fileLink.target = '_blank';
        fileLink.rel = 'noreferrer';
        fileLink.textContent = 'عرض ملف الإعلان';
        media.append(fileLink);
      }

      const shade = document.createElement('span');
      shade.className = 'youtube-ad-shade';
      const badge = document.createElement('span');
      badge.className = 'youtube-ad-badge';
      badge.textContent = 'إعلان';
      media.append(shade, badge);

      const body = document.createElement('div');
      body.className = 'youtube-ad-body';
      const meta = document.createElement('div');
      meta.className = 'youtube-ad-meta';
      const avatar = document.createElement('span');
      avatar.className = 'youtube-ad-avatar';
      const store = window.saeyStoreDetails || {};
      if (store.image) {
        const image = document.createElement('img');
        image.src = store.image;
        image.alt = '';
        image.loading = 'lazy';
        avatar.append(image);
      } else {
        avatar.textContent = String(store.name || 'س').trim().charAt(0) || 'س';
      }

      const copy = document.createElement('div');
      copy.className = 'youtube-ad-copy';
      const title = document.createElement('strong');
      title.textContent = ad.title || 'إعلان المتجر';
      copy.append(title);
      if (ad.caption) {
        const caption = document.createElement('small');
        caption.textContent = ad.caption;
        copy.append(caption);
      }
      meta.append(avatar, copy);

      const actions = document.createElement('div');
      actions.className = 'youtube-ad-actions';
      const actionLink = document.createElement('a');
      actionLink.className = 'youtube-ad-cta store-ad-cta';
      actionLink.href = adActionUrl(ad);
      actionLink.textContent = ad.action_label || ({
        view_product: 'عرض المنتج',
        buy_now: 'تسوق الآن',
        start_chat: 'تواصل مع المتجر',
        view_store: 'عرض المتجر',
      }[ad.action] || 'عرض الإعلان');
      actions.append(actionLink);
      body.append(meta, actions);
      card.append(media, body);
      adList.append(card);
    });
    adsState.hidden = true;
  };

  const loadStoreActiveAds = async (append = false) => {
    if (!storeId) return;

    const requestId = ++adsRequestId;
    const requestedPage = append ? adsPage + 1 : 1;
    if (!append) {
      adsPage = 1;
      adsLastPage = 1;
      adsSection.hidden = false;
      adList.replaceChildren();
      loadMoreAds.hidden = true;
      setAdsState('جارٍ تحميل إعلانات المتجر...', 'loading');
    } else {
      loadMoreAds.disabled = true;
    }

    const query = new URLSearchParams({
      pagination: 'on',
      limit_per_page: '3',
      page: String(requestedPage),
    });

    try {
      const response = await fetch(`${apiOrigin}/api/user/stores/${encodeURIComponent(storeId)}/ads?${query}`, {
        headers: {
          Accept: 'application/json',
          'Accept-Language': 'ar',
          Authorization: `Bearer ${token()}`
        }
      });
      const payload = await response.json();
      if (requestId !== adsRequestId) return;
      if (response.status === 401) {
        setAdsState('انتهت جلسة تسجيل الدخول.', 'auth');
        return;
      }
      if (!response.ok) throw new Error(payload.message || 'تعذر تحميل إعلانات المتجر.');

      renderStoreAds(payload.data, append);
      adsPage = Number(payload.current_page || requestedPage);
      adsLastPage = Number(payload.last_page || adsPage);
      loadMoreAds.hidden = adsPage >= adsLastPage;
    } catch (error) {
      if (requestId === adsRequestId) setAdsState(error.message || 'تعذر تحميل إعلانات المتجر.', 'error');
    } finally {
      loadMoreAds.disabled = false;
    }
  };

  const renderDetails = details => {
    const hero = document.getElementById('storeHero');
    const heroImage = document.getElementById('storeHeroImage');
    const logo = document.getElementById('storeLogo');
    const name = details.name || 'متجر سعي';
    const cover = details.cover || details.image;
    if (cover) {
      heroImage.src = cover;
      heroImage.alt = `واجهة متجر ${name}`;
      hero.hidden = false;
    }
    if (details.image) {
      const image = document.createElement('img');
      image.src = details.image;
      image.alt = `شعار متجر ${name}`;
      logo.replaceChildren(image);
    } else {
      logo.textContent = name.trim().charAt(0) || 'س';
    }

    document.getElementById('storeName').textContent = name;
    const locationLabel = [details.category_name, details.city_name, details.governorate_name].filter(Boolean).join(' · ');
    document.getElementById('storeCategoryLocation').textContent = locationLabel;
    document.getElementById('storeRating').textContent = details.average_rating === null
      ? '—'
      : formatNumber(details.average_rating);
    document.getElementById('storeDistance').textContent = details.distance_km === null
      ? '—'
      : `${formatNumber(details.distance_km)} كم`;
    document.getElementById('storeProductCount').textContent = formatNumber(details.products_count);
    document.getElementById('storeDescription').textContent = details.description || '';
    const address = [details.address, details.city_name, details.governorate_name].filter(Boolean).join('، ');
    document.getElementById('storeAddress').textContent = address || 'لم يحدد المتجر عنوانه بعد.';
    const about = document.getElementById('storeAbout');
    about.hidden = !details.description && !address;

    const phone = [details.phone_code, details.phone].filter(Boolean).join('');
    const phoneLink = document.getElementById('storePhone');
    phoneLink.href = phone ? `tel:${phone}` : '#';
    phoneLink.hidden = !phone;
    document.getElementById('storeChat').href = `14-chat-conversation.html?store=${encodeURIComponent(name)}&store_id=${encodeURIComponent(details.owner_id)}`;

    const map = document.getElementById('storeMap');
    if (details.latitude !== null && details.longitude !== null) {
      map.href = `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(`${details.latitude},${details.longitude}`)}`;
      map.hidden = false;
    } else {
      map.hidden = true;
    }

    const favorite = document.getElementById('favoriteStore');
    favorite.classList.toggle('saved', Boolean(details.is_favorite));
    favorite.setAttribute('aria-pressed', String(Boolean(details.is_favorite)));
    favorite.dataset.storeId = String(details.id);
    favorite.dataset.storeOwnerId = String(details.owner_id);
    window.saeyStoreDetails = details;
    renderPolicy(details.policy);
    profile.hidden = false;
    document.getElementById('storeContent').hidden = false;
    state.hidden = true;
    loadStoreProducts();
    loadStoreActiveAds();
  };

  const load = async () => {
    if (!token()) {
      setState('سجّل الدخول لعرض تفاصيل المتجر.', true);
      return;
    }

    if (!storeId) {
      try {
        const listResponse = await fetch(`${apiOrigin}/api/user/stores/nearby?limit_per_page=1`, {
          headers: {
            Accept: 'application/json',
            'Accept-Language': 'ar',
            Authorization: `Bearer ${token()}`
          }
        });
        const listPayload = await listResponse.json();
        if (!listResponse.ok) throw new Error(listPayload.message || 'تعذر تحميل قائمة المتاجر.');
        storeId = listPayload.data?.[0]?.id ? String(listPayload.data[0].id) : '';
        if (!storeId) {
          setState('لا توجد متاجر نشطة لعرض تفاصيلها حاليًا.');
          return;
        }
        window.saeyStoreId = storeId;
        SaeyUserReport.bind({ type: 'store', itemPath: 'stores', getItemId: () => storeId });
      } catch (error) {
        setState(error.message || 'تعذر تحميل قائمة المتاجر.');
        return;
      }
    }

    const query = new URLSearchParams();
    try {
      const pickup = JSON.parse(localStorage.getItem('saey_pickup_location') || 'null');
      if (Number.isFinite(Number(pickup?.lat)) && Number.isFinite(Number(pickup?.long))) {
        query.set('lat', String(pickup.lat));
        query.set('long', String(pickup.long));
      }
    } catch {}

    try {
      const response = await fetch(`${apiOrigin}/api/user/stores/${encodeURIComponent(storeId)}${query.size ? `?${query}` : ''}`, {
        headers: {
          Accept: 'application/json',
          'Accept-Language': 'ar',
          Authorization: `Bearer ${token()}`
        }
      });
      const payload = await response.json();
      if (response.status === 401) {
        setState('انتهت جلسة تسجيل الدخول. سجّل الدخول مرة أخرى لعرض المتجر.', true);
        return;
      }
      if (!response.ok) throw new Error(payload.message || 'تعذر تحميل تفاصيل المتجر.');
      renderDetails(payload.data);
    } catch (error) {
      setState(error.message || 'تعذر تحميل تفاصيل المتجر.');
    }
  };

  document.getElementById('shareStore').addEventListener('click', async () => {
    try {
      await navigator.share?.({ title: window.saeyStoreDetails?.name || 'متجر سعي', url: location.href });
    } catch {}
  });

  document.getElementById('favoriteStore').addEventListener('click', async event => {
    const button = event.currentTarget;
    if (!token() || !button.dataset.storeId) {
      setState('سجّل الدخول لإضافة المتجر إلى المفضلة.', true);
      return;
    }
    button.disabled = true;
    try {
      const response = await fetch(`${apiOrigin}/api/user/favorites/toggle`, {
        method: 'POST',
        headers: {
          Accept: 'application/json',
          'Content-Type': 'application/json',
          Authorization: `Bearer ${token()}`
        },
        body: JSON.stringify({ type: 'store', favoriteable_id: Number(button.dataset.storeId) })
      });
      const payload = await response.json();
      if (!response.ok) throw new Error(payload.message || 'تعذر تحديث المفضلة.');
      const isFavorite = Boolean(payload.data?.is_favorite);
      button.classList.toggle('saved', isFavorite);
      button.setAttribute('aria-pressed', String(isFavorite));
    } catch (error) {
      setState(error.message || 'تعذر تحديث المفضلة.');
    } finally {
      button.disabled = false;
    }
  });

  productSearch.addEventListener('input', () => {
    clearTimeout(productSearchTimer);
    productSearchTimer = setTimeout(() => loadStoreProducts(), 300);
  });

  productSort.addEventListener('change', () => loadStoreProducts());
  loadMoreProducts.addEventListener('click', () => loadStoreProducts(true));
  loadMoreAds.addEventListener('click', () => loadStoreActiveAds(true));

  load();
})();
