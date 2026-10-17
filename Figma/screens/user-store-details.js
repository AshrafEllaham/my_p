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
  let adsRefreshTimer = null;
  const seenStoreAdImpressions = new Set();
  const token = () => localStorage.getItem('saey_api_token');
  const eventId = () => {
    if (crypto.randomUUID) return crypto.randomUUID();
    return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, character => {
      const value = crypto.getRandomValues(new Uint8Array(1))[0] & 15;
      return (character === 'x' ? value : (value & 3) | 8).toString(16);
    });
  };
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
    if (ad.action === 'view_product' && ad.product_id) {
      return `12-product-details.html?product_id=${encodeURIComponent(ad.product_id)}`;
    }
    if (ad.action === 'buy_now' && ad.product_id) {
      return `20-checkout.html?product_id=${encodeURIComponent(ad.product_id)}`;
    }
    if (ad.action === 'start_chat') {
      const store = window.saeyStoreDetails;
      return `14-chat-conversation.html?store=${encodeURIComponent(store?.name || 'المتجر')}&store_id=${encodeURIComponent(store?.owner_id || '')}`;
    }

    return `19-store-details.html?store_id=${encodeURIComponent(storeId)}`;
  };

  const viewerUrlFor = adId => `25-sponsored-reel.html?placement=storefront&store_id=${encodeURIComponent(storeId)}&ad_id=${encodeURIComponent(adId)}`;

  const trackStoreAd = async (adId, interaction) => {
    const form = new FormData();
    form.append('event_id', eventId());
    const response = await fetch(`${apiOrigin}/api/user/ads/${encodeURIComponent(adId)}/${interaction === 'impression' ? 'impressions' : 'clicks'}`, {
      method: 'POST',
      headers: {
        Accept: 'application/json',
        'Accept-Language': 'ar',
        ...(token() ? { Authorization: `Bearer ${token()}` } : {})
      },
      body: form
    });
    if (!response.ok) throw new Error('تعذر تسجيل التفاعل مع الإعلان.');
  };

  const trackClickAndContinue = (event, adId, href) => {
    if (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey || event.button !== 0) return;
    event.preventDefault();
    trackStoreAd(adId, 'click').catch(() => {}).finally(() => location.assign(href));
  };

  const observeStoreAdImpressions = cards => {
    if (!('IntersectionObserver' in window)) {
      cards.forEach(card => {
        const adId = card.dataset.adId;
        if (seenStoreAdImpressions.has(adId)) return;
        seenStoreAdImpressions.add(adId);
        trackStoreAd(adId, 'impression').catch(() => {});
      });
      return;
    }

    const observer = new IntersectionObserver(entries => {
      entries.forEach(entry => {
        if (!entry.isIntersecting || entry.intersectionRatio < 0.5) return;
        observer.unobserve(entry.target);
        const adId = entry.target.dataset.adId;
        if (seenStoreAdImpressions.has(adId)) return;
        seenStoreAdImpressions.add(adId);
        trackStoreAd(adId, 'impression').catch(() => {});
      });
    }, { threshold: [0.5] });

    cards.forEach(card => observer.observe(card));
  };

  const scheduleStoreAdsRefresh = ads => {
    clearTimeout(adsRefreshTimer);
    const now = Date.now();
    const nextEnd = (Array.isArray(ads) ? ads : [])
      .map(ad => Date.parse(ad.ends_at_iso || ad.ends_at || ''))
      .filter(end => Number.isFinite(end) && end > now)
      .sort((left, right) => left - right)[0];
    const untilNextEnd = Number.isFinite(nextEnd) ? Math.max(nextEnd - now + 500, 250) : 60_000;
    adsRefreshTimer = window.setTimeout(() => loadStoreActiveAds(false, true), Math.min(untilNextEnd, 60_000));
  };

  const renderStoreAds = (ads, append = false) => {
    if (!append) adList.replaceChildren();
    if (!ads?.length) {
      if (!append) setAdsState('لا توجد إعلانات نشطة لهذا المتجر حاليًا.', 'empty');
      return;
    }

    const impressionTargets = [];
    ads.forEach(ad => {
      const card = document.createElement('article');
      card.className = 'youtube-ad compact store-active-ad';
      card.dataset.adId = String(ad.id);

      const media = document.createElement('a');
      media.className = 'youtube-ad-media';
      const viewerHref = viewerUrlFor(ad.id);
      media.href = viewerHref;
      media.setAttribute('aria-label', `عرض الإعلان ${ad.title || ''} بملء الشاشة`);
      media.addEventListener('click', event => trackClickAndContinue(event, ad.id, viewerHref));
      const mediaUrl = ad.media_url || ad.media_path;
      if (ad.media_type === 'video' && ad.media_path) {
        const video = document.createElement('video');
        video.src = mediaUrl;
        video.muted = true;
        video.autoplay = true;
        video.loop = true;
        video.playsInline = true;
        video.preload = 'metadata';
        video.setAttribute('aria-label', ad.title || 'فيديو الإعلان');
        media.append(video);
      } else if (ad.media_type === 'image' && mediaUrl) {
        const image = document.createElement('img');
        image.src = mediaUrl;
        image.alt = ad.title || 'صورة الإعلان';
        image.loading = 'lazy';
        media.append(image);
      } else {
        media.classList.add('store-ad-media-fallback');
        media.textContent = 'عرض الإعلان';
      }

      const shade = document.createElement('span');
      shade.className = 'youtube-ad-shade';
      const badge = document.createElement('span');
      badge.className = 'youtube-ad-badge';
      badge.textContent = 'إعلان';
      media.append(shade, badge);

      const body = document.createElement('div');
      body.className = 'youtube-ad-body';
      const viewerLink = document.createElement('a');
      viewerLink.className = 'youtube-ad-viewer-link';
      viewerLink.href = viewerHref;
      viewerLink.setAttribute('aria-label', 'عرض الإعلان بملء الشاشة');
      viewerLink.addEventListener('click', event => trackClickAndContinue(event, ad.id, viewerHref));
      const meta = document.createElement('div');
      meta.className = 'youtube-ad-meta';
      const avatar = document.createElement('span');
      avatar.className = 'youtube-ad-avatar';
      const store = ad.store || window.saeyStoreDetails || {};
      const avatarUrl = store.avatar || store.image;
      if (avatarUrl) {
        const image = document.createElement('img');
        image.src = avatarUrl;
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
        caption.textContent = `مموّل · ${ad.caption}`;
        copy.append(caption);
      }
      meta.append(avatar, copy);
      viewerLink.append(meta);

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
      actionLink.addEventListener('click', event => trackClickAndContinue(event, ad.id, actionLink.href));
      actions.append(actionLink);
      body.append(viewerLink, actions);
      card.append(media, body);
      adList.append(card);
      impressionTargets.push(card);
    });
    adsState.hidden = true;
    observeStoreAdImpressions(impressionTargets);
  };

  const loadStoreActiveAds = async (append = false, silent = false) => {
    if (!storeId) return;

    if (!append) clearTimeout(adsRefreshTimer);

    const requestId = ++adsRequestId;
    const requestedPage = append ? adsPage + 1 : 1;
    if (!append) {
      adsSection.hidden = false;
      if (!silent) {
        adsPage = 1;
        adsLastPage = 1;
        loadMoreAds.hidden = true;
        adList.replaceChildren();
        setAdsState('جارٍ تحميل إعلانات المتجر...', 'loading');
      }
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

      if (silent && !append) {
        const existingIds = Array.from(adList.querySelectorAll('.store-active-ad'), card => card.dataset.adId);
        const freshIds = Array.isArray(payload.data) ? payload.data.map(ad => String(ad.id)) : [];
        const pageSize = Number(payload.per_page) || freshIds.length;
        const sameEmptyState = freshIds.length === 0
          && existingIds.length === 0
          && adsState.classList.contains('is-empty');
        const unchanged = freshIds.length
          ? freshIds.every((id, index) => existingIds[index] === id)
            && (freshIds.length === pageSize || existingIds.length === freshIds.length)
          : sameEmptyState;
        if (!unchanged) {
          renderStoreAds(payload.data, false);
          adsPage = Number(payload.current_page || 1);
        }
      } else {
        renderStoreAds(payload.data, append);
        adsPage = Number(payload.current_page || requestedPage);
      }
      adsLastPage = Number(payload.last_page || adsPage);
      loadMoreAds.hidden = adsPage >= adsLastPage;
      if (!append) scheduleStoreAdsRefresh(payload.data);
    } catch (error) {
      if (requestId === adsRequestId) {
        if (!silent || !adList.children.length) setAdsState(error.message || 'تعذر تحميل إعلانات المتجر.', 'error');
        if (!append) adsRefreshTimer = window.setTimeout(() => loadStoreActiveAds(false, true), 30_000);
      }
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

  document.addEventListener('visibilitychange', () => {
    if (document.visibilityState === 'visible') loadStoreActiveAds(false, true);
  });

  load();
})();
