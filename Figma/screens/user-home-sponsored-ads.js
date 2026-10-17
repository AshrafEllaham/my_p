(() => {
  const homeList = document.getElementById('homeSponsoredAds');
  const categoryList = document.getElementById('categorySponsoredAds');
  const list = homeList || categoryList;
  if (!list) return;
  const placement = categoryList ? 'category' : 'home';
  const section = list.closest('.youtube-ad-section');

  const apiOrigin = location.protocol === 'file:'
    ? 'http://127.0.0.1:8000'
    : ['localhost', '127.0.0.1'].includes(location.hostname)
      ? `${location.protocol}//${location.hostname}:8000`
      : location.origin;
  const token = () => localStorage.getItem('saey_api_token');
  let rotationTimer = null;
  let isLoading = false;
  let loadRequestId = 0;
  const icon = {
    shop: '<svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="9" cy="20" r="1"/><circle cx="19" cy="20" r="1"/><path d="M3 4h2l2.2 10.2a2 2 0 0 0 2 1.6h8.7a2 2 0 0 0 2-1.6L21 8H7"/></svg>',
    chat: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7.9 20A9 9 0 1 0 4 16.1L2 22Z"/><path d="M8 11h8M8 15h5"/></svg>',
    play: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m8 5 11 7-11 7Z"/></svg>'
  };

  const eventId = () => {
    if (crypto.randomUUID) return crypto.randomUUID();
    return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, character => {
      const value = crypto.getRandomValues(new Uint8Array(1))[0] & 15;
      return (character === 'x' ? value : (value & 3) | 8).toString(16);
    });
  };

  const showState = (message, action = null) => {
    window.clearTimeout(rotationTimer);
    rotationTimer = null;
    list.replaceChildren();
    const state = document.createElement('div');
    state.className = 'home-sponsored-ads-state';
    state.textContent = message;
    if (action) {
      const control = action.href ? document.createElement('a') : document.createElement('button');
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

  const destinationFor = ad => {
    const target = ad.target || {};
    const storeId = target.store_id || ad.store?.id;
    const productId = target.product_id;
    switch (ad.action) {
      case 'view_product':
        return productId ? `12-product-details.html?product_id=${encodeURIComponent(productId)}` : `19-store-details.html?store_id=${encodeURIComponent(storeId || '')}`;
      case 'buy_now':
        return productId ? `20-checkout.html?product_id=${encodeURIComponent(productId)}` : `19-store-details.html?store_id=${encodeURIComponent(storeId || '')}`;
      case 'start_chat':
        return `14-chat-conversation.html?store_id=${encodeURIComponent(storeId || '')}`;
      case 'view_store':
      default:
        return `19-store-details.html?store_id=${encodeURIComponent(storeId || '')}`;
    }
  };

  const categoryId = () => String(window.saeyCategoryId || '');
  const viewerFor = adId => {
    const params = new URLSearchParams({ placement, ad_id: String(adId) });
    if (placement === 'category' && categoryId()) params.set('category_id', categoryId());
    return `25-sponsored-reel.html?${params.toString()}`;
  };

  const recordClickAndContinue = (event, adId, href) => {
    if (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey || event.button !== 0) return;
    event.preventDefault();
    track(adId, 'click').catch(() => {}).finally(() => location.assign(href));
  };

  const makeLink = (href, adId, label, iconMarkup, className) => {
    const link = document.createElement('a');
    link.className = className;
    link.href = href;
    link.setAttribute('aria-label', label);
    link.title = label;
    link.innerHTML = iconMarkup;
    link.addEventListener('click', event => recordClickAndContinue(event, adId, href));
    return link;
  };

  const formatDuration = seconds => {
    const duration = Math.max(0, Math.floor(seconds || 0));
    return `${Math.floor(duration / 60)}:${String(duration % 60).padStart(2, '0')}`;
  };

  const createCard = ad => {
    const card = document.createElement('article');
    card.className = `youtube-ad api-sponsored-ad api-${placement}-sponsored-ad`;
    card.dataset.adId = ad.id;

    const href = viewerFor(ad.id);
    const media = document.createElement('a');
    media.className = 'youtube-ad-media';
    media.href = href;
    media.setAttribute('aria-label', 'عرض الإعلان بملء الشاشة');
    media.addEventListener('click', event => recordClickAndContinue(event, ad.id, href));

    if (ad.media_type === 'video') {
      const video = document.createElement('video');
      video.src = ad.media_url;
      video.muted = true;
      video.autoplay = true;
      video.loop = true;
      video.playsInline = true;
      video.setAttribute('aria-label', ad.title || 'إعلان فيديو');
      media.append(video);

      const duration = document.createElement('span');
      duration.className = 'youtube-ad-duration';
      duration.hidden = true;
      video.addEventListener('loadedmetadata', () => {
        duration.textContent = formatDuration(video.duration);
        duration.hidden = false;
      }, { once: true });
      media.append(duration);
    } else {
      const image = document.createElement('img');
      image.src = ad.media_url;
      image.alt = ad.title || 'إعلان ممول';
      image.loading = 'lazy';
      media.append(image);
    }

    const shade = document.createElement('span');
    shade.className = 'youtube-ad-shade';
    const badge = document.createElement('span');
    badge.className = 'youtube-ad-badge';
    badge.textContent = 'إعلان';
    const play = document.createElement('span');
    play.className = 'youtube-ad-play';
    play.innerHTML = icon.play;
    media.append(shade, badge, play);

    const body = document.createElement('div');
    body.className = 'youtube-ad-body';
    const viewerLink = document.createElement('a');
    viewerLink.className = 'youtube-ad-viewer-link';
    viewerLink.href = href;
    viewerLink.setAttribute('aria-label', 'عرض الإعلان بملء الشاشة');
    viewerLink.addEventListener('click', event => recordClickAndContinue(event, ad.id, href));
    const meta = document.createElement('div');
    meta.className = 'youtube-ad-meta';
    const avatar = document.createElement('span');
    avatar.className = 'youtube-ad-avatar';
    const storeName = ad.store?.name || 'متجر سعي';
    if (ad.store?.avatar) {
      const avatarImage = document.createElement('img');
      avatarImage.src = ad.store.avatar;
      avatarImage.alt = '';
      avatar.append(avatarImage);
    } else {
      avatar.textContent = storeName.trim().charAt(0) || 'س';
    }

    const copy = document.createElement('span');
    copy.className = 'youtube-ad-copy';
    const title = document.createElement('strong');
    title.textContent = ad.title || ad.caption || 'إعلان ممول';
    const sponsor = document.createElement('small');
    const sponsorLabel = document.createElement('b');
    sponsorLabel.textContent = 'مموّل';
    sponsor.append(sponsorLabel, document.createTextNode(` · ${storeName}`));
    copy.append(title, sponsor);
    meta.append(avatar, copy);

    const actions = document.createElement('div');
    actions.className = 'youtube-ad-actions';
    const isChatAction = ad.action === 'start_chat';
    const actionLabel = ad.action_label || (isChatAction ? 'دردشة' : 'تسوّق الآن');
    const actionHref = destinationFor(ad);
    actions.append(makeLink(actionHref, ad.id, actionLabel, isChatAction ? icon.chat : icon.shop, 'youtube-ad-cta'));
    if (ad.store?.id) {
      const secondaryHref = isChatAction
        ? `19-store-details.html?store_id=${encodeURIComponent(ad.store.id)}`
        : `14-chat-conversation.html?store_id=${encodeURIComponent(ad.store.id)}`;
      const secondaryLabel = isChatAction ? `زيارة متجر ${storeName}` : `دردشة مع ${storeName}`;
      actions.append(makeLink(secondaryHref, ad.id, secondaryLabel, isChatAction ? icon.shop : icon.chat, 'youtube-ad-chat'));
    }
    viewerLink.append(meta);
    body.append(viewerLink, actions);
    card.append(media, body);

    return card;
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
    const payload = await response.json().catch(() => ({}));
    if (!response.ok) throw new Error(payload.message || 'تعذر تسجيل التفاعل مع الإعلان.');
    return payload;
  }

  const observeImpressions = cards => {
    if (!('IntersectionObserver' in window)) {
      cards.forEach(card => track(card.dataset.adId, 'impression').catch(() => {}));
      return;
    }

    const observer = new IntersectionObserver(entries => {
      entries.forEach(entry => {
        if (!entry.isIntersecting || entry.intersectionRatio < 0.5) return;
        observer.unobserve(entry.target);
        track(entry.target.dataset.adId, 'impression').catch(() => {});
      });
    }, { threshold: [0.5] });

    cards.forEach(card => observer.observe(card));
  };

  const scheduleRefresh = (ad, delay = 60_000) => {
    window.clearTimeout(rotationTimer);
    const endsAt = Date.parse(ad?.ends_at || '');

    if (!Number.isFinite(endsAt)) {
      rotationTimer = window.setTimeout(load, delay);
      return;
    }

    const remaining = Math.min(endsAt - Date.now() + 500, delay);
    const maximumTimeout = 2_147_483_647;
    if (remaining > maximumTimeout) {
      rotationTimer = window.setTimeout(() => scheduleRefresh(ad), maximumTimeout);
      return;
    }

    rotationTimer = window.setTimeout(load, Math.max(remaining, 250));
  };

  const nextAdInSequence = ads => ads.slice().sort((left, right) => {
    const endTime = ad => {
      const value = Date.parse(ad.ends_at || '');
      return Number.isFinite(value) ? value : Number.POSITIVE_INFINITY;
    };
    const leftEnd = endTime(left);
    const rightEnd = endTime(right);
    if (leftEnd !== rightEnd) return leftEnd < rightEnd ? -1 : 1;

    const startDifference = Date.parse(left.starts_at || '') - Date.parse(right.starts_at || '');
    if (Number.isFinite(startDifference) && startDifference !== 0) return startDifference;
    return Number(left.id) - Number(right.id);
  })[0] || null;

  async function load() {
    if (isLoading) return;
    if (placement === 'category' && !categoryId()) {
      list.replaceChildren();
      section.hidden = true;
      return;
    }
    isLoading = true;
    const requestId = ++loadRequestId;
    section.hidden = false;

    if (!list.querySelector('.youtube-ad')) showState('جارٍ تحميل الإعلانات الممولة...');

    try {
      let endpoint = `${apiOrigin}/api/user/ads?placement=home&limit_per_page=5&page=1`;
      if (placement === 'category') {
        const params = new URLSearchParams({ placement, category_id: categoryId(), limit_per_page: '5', page: '1' });
        endpoint = `${apiOrigin}/api/user/ads?${params}`;
      }
      const response = await fetch(endpoint, {
        headers: {
          Accept: 'application/json',
          'Accept-Language': 'ar',
          ...(token() ? { Authorization: `Bearer ${token()}` } : {})
        }
      });
      const payload = await response.json();
      if (requestId !== loadRequestId) return;
      if (response.status === 401) {
        showState('سجّل الدخول لعرض الإعلانات الممولة.', { label: 'تسجيل الدخول', href: '01-login.html' });
        return;
      }
      if (!response.ok) throw new Error(payload.message || 'تعذر تحميل الإعلانات الممولة.');
      const ad = nextAdInSequence(Array.isArray(payload.data) ? payload.data : []);
      if (!ad) {
        if (placement === 'category') {
          window.clearTimeout(rotationTimer);
          list.replaceChildren();
          section.hidden = true;
          rotationTimer = window.setTimeout(load, 60_000);
          return;
        }
        showState('لا توجد إعلانات ممولة متاحة حاليًا.');
        rotationTimer = window.setTimeout(load, 60_000);
        return;
      }

      const currentCard = list.querySelector('.youtube-ad');
      if (currentCard?.dataset.adId === String(ad.id)) {
        scheduleRefresh(ad);
        return;
      }

      window.clearTimeout(rotationTimer);
      const card = createCard(ad);
      list.replaceChildren(card);
      observeImpressions([card]);
      scheduleRefresh(ad);
    } catch (error) {
      if (requestId !== loadRequestId) return;
      showState(error.message || 'تعذر تحميل الإعلانات الممولة.', { label: 'إعادة المحاولة' });
      rotationTimer = window.setTimeout(load, 30_000);
    } finally {
      if (requestId === loadRequestId) isLoading = false;
    }
  }

  document.addEventListener('visibilitychange', () => {
    if (document.visibilityState === 'visible') load();
  });

  if (placement === 'category') {
    window.addEventListener('saey:category-changed', event => {
      window.saeyCategoryId = String(event.detail?.categoryId || '');
      window.clearTimeout(rotationTimer);
      loadRequestId += 1;
      isLoading = false;
      list.replaceChildren();
      section.hidden = false;
      load();
    });
  }

  load();
})();
