(() => {
  const staticAds = [
    {
      id: 'electronics',
      avatar: 'ت',
      store: 'التقنية الحديثة',
      title: 'خصم حتى ٢٠٪ على أحدث الإلكترونيات',
      description: 'أحدث الأجهزة والإكسسوارات بعروض مختارة لفترة محدودة.',
      slides: [
        'https://images.unsplash.com/photo-1498049794561-7780e7231661?auto=format&fit=crop&w=1000&q=88',
        'https://images.unsplash.com/photo-1526738549149-8e07eca6c147?auto=format&fit=crop&w=1000&q=88',
        'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?auto=format&fit=crop&w=1000&q=88'
      ],
      storeUrl: '19-store-details.html',
      cta: 'تسوّق الآن',
      ctaUrl: '19-store-details.html'
    },
    {
      id: 'jackets',
      avatar: 'ن',
      store: 'النجم الساطع',
      title: 'خصم ١٠٪ على تشكيلة الجاكيتات الجديدة',
      description: 'تشكيلة موسمية أنيقة متاحة الآن للاستلام من المتجر.',
      slides: [
        'https://images.unsplash.com/photo-1551028719-00167b16eac5?auto=format&fit=crop&w=1000&q=88',
        'https://images.unsplash.com/photo-1548883354-7622d03aca27?auto=format&fit=crop&w=1000&q=88',
        'https://images.unsplash.com/photo-1525507119028-ed4c629a60a3?auto=format&fit=crop&w=1000&q=88'
      ],
      storeUrl: '19-store-details.html',
      cta: 'عرض المنتج',
      ctaUrl: '12-product-details.html'
    },
    {
      id: 'fashion',
      avatar: 'ن',
      store: 'النجم الساطع',
      title: 'تشكيلة جديدة داخل قسم الأزياء',
      description: 'اختيارات جديدة من الملابس اليومية والأكثر طلبًا.',
      slides: [
        'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?auto=format&fit=crop&w=1000&q=88',
        'https://images.unsplash.com/photo-1445205170230-053b83016050?auto=format&fit=crop&w=1000&q=88',
        'https://images.unsplash.com/photo-1525507119028-ed4c629a60a3?auto=format&fit=crop&w=1000&q=88'
      ],
      storeUrl: '19-store-details.html',
      cta: 'عرض المتجر',
      ctaUrl: '19-store-details.html'
    }
  ];

  const viewer = document.getElementById('reelViewer');
  if (!viewer) return;

  const image = document.getElementById('reelMedia');
  const video = document.getElementById('reelVideo');
  const progress = document.getElementById('reelProgress');
  const state = document.getElementById('reelState');
  const query = new URLSearchParams(location.search);
  const useApiFeed = query.has('placement') || query.has('ad_id') || !query.has('ad');
  const apiOrigin = location.protocol === 'file:'
    ? 'http://127.0.0.1:8000'
    : ['localhost', '127.0.0.1'].includes(location.hostname)
      ? `${location.protocol}//${location.hostname}:8000`
      : location.origin;
  const token = () => localStorage.getItem('saey_api_token');
  let ads = [];
  let adIndex = 0;
  let slideIndex = 0;
  let timer = null;
  let paused = false;
  let touchStartY = null;

  const eventId = () => {
    if (crypto.randomUUID) return crypto.randomUUID();
    return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, character => {
      const value = crypto.getRandomValues(new Uint8Array(1))[0] & 15;
      return (character === 'x' ? value : (value & 3) | 8).toString(16);
    });
  };

  const destinationFor = ad => {
    const target = ad.target || {};
    const storeId = target.store_id || ad.store?.id;
    const productId = target.product_id;
    switch (ad.action) {
      case 'view_product':
        return productId
          ? `12-product-details.html?product_id=${encodeURIComponent(productId)}`
          : `19-store-details.html?store_id=${encodeURIComponent(storeId || '')}`;
      case 'buy_now':
        return productId
          ? `20-checkout.html?product_id=${encodeURIComponent(productId)}`
          : `19-store-details.html?store_id=${encodeURIComponent(storeId || '')}`;
      case 'start_chat':
        return `14-chat-conversation.html?store_id=${encodeURIComponent(storeId || '')}`;
      case 'view_store':
      default:
        return `19-store-details.html?store_id=${encodeURIComponent(storeId || '')}`;
    }
  };

  const setTrackedLink = (link, href, adId) => {
    link.href = href;
    link.onclick = event => {
      if (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey || event.button !== 0) return;
      event.preventDefault();
      if (!adId) {
        location.assign(href);
        return;
      }
      track(adId, 'click').catch(() => {}).finally(() => location.assign(href));
    };
  };

  async function track(adId, interaction) {
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
  }

  const showState = (message, action = null) => {
    state.replaceChildren(document.createTextNode(message));
    if (action) {
      const button = action.href ? document.createElement('a') : document.createElement('button');
      button.textContent = action.label;
      if (action.href) button.href = action.href;
      else {
        button.type = 'button';
        button.addEventListener('click', loadApiAds, { once: true });
      }
      state.append(button);
    }
    state.hidden = false;
  };

  const hideState = () => {
    state.hidden = true;
    state.replaceChildren();
  };

  const renderProgress = (count, activeIndex, slideCount = 1) => {
    progress.replaceChildren();
    for (let index = 0; index < count; index += 1) {
      const segment = document.createElement('i');
      segment.className = index < activeIndex ? 'done' : index === activeIndex ? 'active' : '';
      if (index === activeIndex && slideCount > 1) {
        segment.dataset.slideCount = String(slideCount);
        segment.dataset.slideIndex = String(slideIndex);
      }
      progress.append(segment);
    }
  };

  const setAvatar = (avatar, imageUrl, fallback) => {
    avatar.replaceChildren();
    if (imageUrl) {
      const avatarImage = document.createElement('img');
      avatarImage.src = imageUrl;
      avatarImage.alt = '';
      avatar.append(avatarImage);
    } else {
      avatar.textContent = fallback || 'س';
    }
  };

  const bindActionLinks = (ad, storeId) => {
    const cta = document.getElementById('reelCta');
    const chat = document.getElementById('reelChat');
    const storeLink = document.getElementById('reelStore');
    const actionLabel = ad.action_label || ad.cta || 'عرض الإعلان';
    const ctaHref = ad.ctaUrl || destinationFor(ad);
    setTrackedLink(cta, ctaHref, ad.apiId);
    cta.setAttribute('aria-label', actionLabel);
    cta.title = actionLabel;
    cta.innerHTML = ad.action === 'start_chat'
      ? '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7.9 20A9 9 0 1 0 4 16.1L2 22Z"/><path d="M8 11h8M8 15h5"/></svg>'
      : '<svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="9" cy="20" r="1"/><circle cx="19" cy="20" r="1"/><path d="M3 4h2l2.2 10.2a2 2 0 0 0 2 1.6h8.7a2 2 0 0 0 2-1.6L21 8H7"/></svg>';
    chat.href = `14-chat-conversation.html?store_id=${encodeURIComponent(storeId || '')}`;
    chat.onclick = event => {
      if (!ad.apiId || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey || event.button !== 0) return;
      event.preventDefault();
      const href = chat.href;
      track(ad.apiId, 'click').catch(() => {}).finally(() => location.assign(href));
    };
    setTrackedLink(storeLink, ad.storeUrl || `19-store-details.html?store_id=${encodeURIComponent(storeId || '')}`, ad.apiId);
  };

  const setAdMedia = ad => {
    clearTimeout(timer);
    video.pause();
    video.removeAttribute('src');
    video.load();
    image.hidden = ad.media_type === 'video';
    video.hidden = ad.media_type !== 'video';
    if (ad.media_type === 'video') {
      video.src = ad.media_url;
      video.onended = () => showNextAd(1);
      video.play().catch(() => {});
    } else {
      video.onended = null;
      image.src = ad.media_url || ad.slides?.[slideIndex] || '';
      image.alt = ad.title || 'إعلان ممول';
    }
  };

  const updateUrl = ad => {
    if (!ad.apiId) return;
    const params = new URLSearchParams(location.search);
    params.set('placement', 'home');
    params.set('ad_id', ad.apiId);
    params.delete('ad');
    history.replaceState(null, '', `${location.pathname}?${params.toString()}`);
  };

  const renderApiAd = (index, updateHistory = true) => {
    if (!ads.length) return;
    adIndex = index;
    slideIndex = 0;
    const ad = ads[adIndex];
    const storeId = ad.target?.store_id || ad.store?.id;
    const storeName = ad.store?.name || 'متجر سعي';
    const storeLink = document.getElementById('reelStore');

    setAdMedia(ad);
    document.getElementById('reelStoreName').textContent = storeName;
    document.getElementById('reelTitle').textContent = ad.title || 'إعلان ممول';
    document.getElementById('reelDescription').textContent = ad.caption || 'اكتشف ما يقدمه المتجر.';
    setAvatar(document.getElementById('reelAvatar'), ad.store?.avatar, storeName.trim().charAt(0));
    document.querySelector('.reel-header > span').innerHTML = '<b>إعلان</b> ممول';
    document.getElementById('reelHint').textContent = 'اسحب لأسفل للإعلان التالي';
    document.getElementById('reelHint').hidden = ads.length < 2;
    const toggle = document.getElementById('toggleReel');
    toggle.hidden = ad.media_type !== 'video';
    paused = false;
    viewer.classList.remove('is-paused');
    document.getElementById('playLabel').textContent = 'إيقاف';
    storeLink.hidden = !storeId;
    bindActionLinks({ ...ad, apiId: ad.id }, storeId);
    renderProgress(ads.length, adIndex);
    hideState();
    if (updateHistory) updateUrl({ ...ad, apiId: ad.id });
    track(ad.id, 'impression').catch(() => {});
  };

  const loadApiAds = async () => {
    clearTimeout(timer);
    showState('جارٍ تحميل الإعلانات...');
    try {
      const fetchPage = async page => {
        const response = await fetch(`${apiOrigin}/api/user/ads?placement=home&limit_per_page=100&page=${page}`, {
          headers: {
            Accept: 'application/json',
            'Accept-Language': 'ar',
            ...(token() ? { Authorization: `Bearer ${token()}` } : {})
          }
        });
        const payload = await response.json().catch(() => ({}));
        if (response.status === 401) {
          showState('سجّل الدخول لعرض الإعلانات الممولة.', { label: 'تسجيل الدخول', href: '01-login.html' });
          throw new Error('تسجيل الدخول مطلوب.');
        }
        if (!response.ok) throw new Error(payload.message || 'تعذر تحميل الإعلانات الممولة.');
        return payload;
      };

      const firstPage = await fetchPage(1);
      const results = Array.isArray(firstPage.data) ? [...firstPage.data] : [];
      const perPage = Number(firstPage.per_page) || 100;
      const lastPage = Number(firstPage.last_page) || Math.ceil((Number(firstPage.total) || results.length) / perPage);
      for (let page = 2; page <= lastPage; page += 1) {
        const payload = await fetchPage(page);
        if (Array.isArray(payload.data)) results.push(...payload.data);
      }

      ads = results.sort((left, right) => {
        const leftEnd = Date.parse(left.ends_at || '');
        const rightEnd = Date.parse(right.ends_at || '');
        const orderedLeftEnd = Number.isFinite(leftEnd) ? leftEnd : Number.POSITIVE_INFINITY;
        const orderedRightEnd = Number.isFinite(rightEnd) ? rightEnd : Number.POSITIVE_INFINITY;
        if (orderedLeftEnd !== orderedRightEnd) return orderedLeftEnd - orderedRightEnd;
        const leftStart = Date.parse(left.starts_at || '');
        const rightStart = Date.parse(right.starts_at || '');
        if (Number.isFinite(leftStart) && Number.isFinite(rightStart) && leftStart !== rightStart) return leftStart - rightStart;
        return Number(left.id) - Number(right.id);
      });

      if (!ads.length) {
        showState('لا توجد إعلانات ممولة متاحة حاليًا.');
        document.getElementById('reelHint').hidden = true;
        return;
      }

      const requestedAdId = query.get('ad_id');
      const requestedIndex = ads.findIndex(ad => String(ad.id) === requestedAdId);
      renderApiAd(requestedIndex >= 0 ? requestedIndex : 0, requestedIndex < 0);
    } catch (error) {
      if (error.message === 'تسجيل الدخول مطلوب.') return;
      showState(error.message || 'تعذر تحميل الإعلانات الممولة.', { label: 'إعادة المحاولة' });
    }
  };

  const showNextAd = direction => {
    if (!ads.length) return;
    if (ads.length === 1) {
      showState('لا توجد إعلانات أخرى متاحة حاليًا.');
      window.setTimeout(hideState, 1800);
      return;
    }
    renderApiAd((adIndex + direction + ads.length) % ads.length);
  };

  const renderStaticAd = index => {
    clearTimeout(timer);
    paused = false;
    viewer.classList.remove('is-paused', 'is-feed');
    adIndex = (index + staticAds.length) % staticAds.length;
    slideIndex = 0;
    const ad = staticAds[adIndex];
    document.getElementById('toggleReel').hidden = false;
    image.hidden = false;
    video.hidden = true;
    video.pause();
    image.src = ad.slides[0];
    image.alt = ad.title;
    document.getElementById('reelAvatar').textContent = ad.avatar;
    document.getElementById('reelStoreName').textContent = ad.store;
    document.getElementById('reelTitle').textContent = ad.title;
    document.getElementById('reelDescription').textContent = ad.description;
    document.querySelector('.reel-header > span').innerHTML = '<b>إعلان</b> ممول';
    document.getElementById('reelHint').textContent = 'اسحب لأسفل للإعلان التالي';
    document.getElementById('reelHint').hidden = false;
    document.getElementById('reelStore').hidden = false;
    bindActionLinks({ ...ad, action_label: ad.cta, storeUrl: ad.storeUrl, ctaUrl: ad.ctaUrl }, null);
    renderProgress(ad.slides.length, slideIndex, ad.slides.length);
    hideState();
    updateStaticSlideTimer();
    const params = new URLSearchParams(location.search);
    params.set('ad', ad.id);
    history.replaceState(null, '', `${location.pathname}?${params.toString()}`);
  };

  const updateStaticSlideTimer = () => {
    clearTimeout(timer);
    if (paused || useApiFeed) return;
    timer = window.setTimeout(() => {
      const ad = staticAds[adIndex];
      slideIndex = (slideIndex + 1) % ad.slides.length;
      image.src = ad.slides[slideIndex];
      renderProgress(ad.slides.length, slideIndex, ad.slides.length);
      updateStaticSlideTimer();
    }, 4200);
  };

  document.getElementById('closeReel').addEventListener('click', () => {
    if (history.length > 1) history.back();
    else location.assign('08-user-home.html');
  });
  document.getElementById('nextReel').addEventListener('click', () => {
    if (useApiFeed) showNextAd(1);
    else renderStaticAd(adIndex + 1);
  });
  document.getElementById('likeReel').addEventListener('click', event => event.currentTarget.classList.toggle('liked'));
  document.getElementById('shareReel').addEventListener('click', async () => {
    try {
      const currentAd = ads[adIndex] || staticAds[adIndex];
      await navigator.share?.({ title: currentAd?.title || 'إعلان ممول', url: location.href });
    } catch (error) {}
  });
  document.getElementById('toggleReel').addEventListener('click', () => {
    paused = !paused;
    viewer.classList.toggle('is-paused', paused);
    document.getElementById('playLabel').textContent = paused ? 'تشغيل' : 'إيقاف';
    if (useApiFeed && !video.hidden) {
      if (paused) video.pause();
      else video.play().catch(() => {});
    } else {
      updateStaticSlideTimer();
    }
  });

  viewer.addEventListener('touchstart', event => {
    touchStartY = event.changedTouches[0].clientY;
  }, { passive: true });
  viewer.addEventListener('touchend', event => {
    if (touchStartY === null) return;
    const distance = event.changedTouches[0].clientY - touchStartY;
    touchStartY = null;
    if (Math.abs(distance) <= 55) return;
    if (useApiFeed) showNextAd(distance > 0 ? 1 : -1);
    else renderStaticAd(adIndex + (distance > 0 ? 1 : -1));
  }, { passive: true });
  window.addEventListener('keydown', event => {
    if (event.key === 'Escape') document.getElementById('closeReel').click();
    if (event.key === 'ArrowDown') useApiFeed ? showNextAd(1) : renderStaticAd(adIndex + 1);
    if (event.key === 'ArrowUp') useApiFeed ? showNextAd(-1) : renderStaticAd(adIndex - 1);
  });

  viewer.classList.toggle('is-feed', useApiFeed);
  if (useApiFeed) loadApiAds();
  else {
    const requestedId = query.get('ad');
    const requestedIndex = staticAds.findIndex(ad => ad.id === requestedId);
    renderStaticAd(requestedIndex >= 0 ? requestedIndex : 0);
  }
})();
