(() => {
  const homeList = document.getElementById('homeContestList');
  const homeState = document.getElementById('homeContestState');
  const publicList = document.getElementById('publicContestList');
  const token = () => localStorage.getItem('saey_api_token') || '';
  const apiOrigin = location.protocol === 'file:'
    ? 'http://127.0.0.1:8000'
    : ['localhost', '127.0.0.1'].includes(location.hostname)
      ? `${location.protocol}//${location.hostname}:8000`
      : location.origin;
  const icons = {
    gift: '<svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="8" width="18" height="13" rx="2"/><path d="M12 8v13M3 12h18M12 8H7.5a2.5 2.5 0 1 1 2.2-3.7L12 8Zm0 0h4.5a2.5 2.5 0 1 0-2.2-3.7L12 8Z"/></svg>',
    clock: '<svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>',
    store: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 9h18l-1 12H4L3 9Z"/><path d="M2 9 4 3h16l2 6M9 13v4m6-4v4"/></svg>',
    check: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m8.5 12 2.3 2.3 4.7-5"/><circle cx="12" cy="12" r="9"/></svg>'
  };

  const dateLabel = (value, withPrefix = false) => {
    const date = new Date(value);
    if (Number.isNaN(date.getTime())) return withPrefix ? 'انتهت المسابقة' : 'موعد المسابقة';
    const formatted = date.toLocaleDateString('ar-EG', { day: 'numeric', month: 'short' });
    return withPrefix ? `حتى ${formatted}` : formatted;
  };

  const imageFor = item => item.image || item.store?.image || '';

  const makeImage = (item, home = false) => {
    const image = document.createElement('span');
    image.className = home ? 'home-contest-art' : 'public-contest-card-image';
    const source = imageFor(item);
    if (source) {
      const photo = document.createElement('img');
      photo.src = source;
      photo.alt = item.store?.name ? `مسابقة من متجر ${item.store.name}` : 'صورة المسابقة';
      photo.loading = 'lazy';
      image.append(photo);
    } else {
      image.classList.add('contest-image-placeholder');
      image.innerHTML = icons.gift;
    }
    if (home) {
      const shade = document.createElement('span');
      shade.className = 'home-contest-art-shade';
      const gift = document.createElement('span');
      gift.className = 'home-contest-gift';
      gift.innerHTML = icons.gift;
      image.append(shade, gift);
    } else {
      const label = document.createElement('span');
      label.textContent = item.status === 'available_now' ? 'مسابقة متاحة' : 'مسابقة منتهية';
      image.append(label);
    }
    return image;
  };

  const homeCard = item => {
    const card = document.createElement('a');
    card.className = 'home-contest-card';
    card.href = `27-competition-details.html?id=${encodeURIComponent(item.id)}`;
    const copy = document.createElement('span');
    copy.className = 'home-contest-copy';
    const title = document.createElement('h3');
    title.textContent = item.title;
    const prize = document.createElement('p');
    prize.className = 'home-contest-prize';
    prize.textContent = item.prize;
    const store = document.createElement('span');
    store.className = 'home-contest-store';
    const storeName = document.createElement('span');
    const storeLabel = document.createElement('small');
    storeLabel.textContent = 'مقدمة من';
    const storeValue = document.createElement('strong');
    storeValue.textContent = item.store?.name || 'متجر سعي';
    storeName.append(storeLabel, storeValue);
    const badge = document.createElement('i');
    badge.setAttribute('aria-label', 'مسابقة من متجر سعي');
    badge.innerHTML = icons.check;
    store.append(storeName, badge);
    const deadline = document.createElement('span');
    deadline.className = 'home-contest-footer';
    deadline.innerHTML = icons.clock;
    const deadlineLabel = document.createElement('span');
    deadlineLabel.textContent = dateLabel(item.ends_at, true);
    deadline.append(deadlineLabel);
    copy.append(title, prize, store, deadline);
    card.append(copy, makeImage(item, true));
    return card;
  };

  const publicCard = item => {
    const card = document.createElement('a');
    card.className = 'public-contest-card';
    if (item.status === 'ended') card.classList.add('completed');
    card.href = `27-competition-details.html?id=${encodeURIComponent(item.id)}`;
    const body = document.createElement('span');
    body.className = 'public-contest-card-body';
    const top = document.createElement('span');
    top.className = 'public-contest-card-top';
    const status = document.createElement('span');
    status.className = `contest-status${item.status === 'ended' ? ' completed' : ''}`;
    status.textContent = item.status === 'available_now' ? 'متاحة الآن' : 'انتهت';
    const date = document.createElement('time');
    date.textContent = item.status === 'available_now' ? `حتى ${dateLabel(item.ends_at)}` : dateLabel(item.ends_at);
    top.append(status, date);
    const title = document.createElement('h3');
    title.textContent = item.title;
    const prize = document.createElement('p');
    prize.textContent = item.prize;
    const store = document.createElement('span');
    store.className = 'public-contest-card-store';
    store.innerHTML = icons.store;
    store.append(document.createTextNode(item.store?.name || 'متجر سعي'));
    body.append(top, title, prize, store);
    card.append(makeImage(item), body);
    return card;
  };

  const requestPage = async (status, page, limit) => {
    const query = new URLSearchParams({
      status,
      pagination: 'on',
      limit_per_page: String(limit),
      page: String(page)
    });
    const response = await fetch(`${apiOrigin}/api/user/competitions?${query}`, {
      headers: {
        Accept: 'application/json',
        'Accept-Language': document.documentElement.lang || 'ar',
        ...(token() ? { Authorization: `Bearer ${token()}` } : {})
      }
    });
    const payload = await response.json().catch(() => ({}));
    if (response.status === 401) {
      const error = new Error('سجّل الدخول لعرض مسابقات المتاجر.');
      error.status = response.status;
      throw error;
    }
    if (!response.ok || payload.code >= 400) throw new Error(payload.message || 'تعذر تحميل المسابقات.');
    return payload;
  };

  const stateCard = (container, message, action = null) => {
    const state = document.createElement('div');
    state.className = 'public-contest-empty';
    const title = document.createElement('h3');
    title.textContent = message;
    state.append(title);
    if (action) {
      const button = action.href ? document.createElement('a') : document.createElement('button');
      button.className = 'contest-state-action';
      button.textContent = action.label;
      if (action.href) button.href = action.href;
      else {
        button.type = 'button';
        button.addEventListener('click', action.onClick);
      }
      state.append(button);
    }
    container.replaceChildren(state);
  };

  async function loadHomeCompetitions() {
    if (!homeList || !homeState) return;
    homeState.hidden = false;
    homeState.textContent = 'جارٍ تحميل مسابقات المتاجر...';
    try {
      const result = await requestPage('available_now', 1, 2);
      homeList.replaceChildren(...(result.data || []).map(homeCard));
      homeState.replaceChildren();
      if (!result.data?.length) {
        homeState.textContent = 'لا توجد مسابقات متاحة الآن.';
      } else {
        homeState.hidden = true;
      }
    } catch (error) {
      homeState.replaceChildren(document.createTextNode(error.message || 'تعذر تحميل مسابقات المتاجر.'));
      const action = error.status === 401 ? document.createElement('a') : document.createElement('button');
      action.className = 'contest-state-action';
      action.textContent = error.status === 401 ? 'تسجيل الدخول' : 'إعادة المحاولة';
      if (error.status === 401) action.href = '01-login.html';
      else {
        action.type = 'button';
        action.addEventListener('click', loadHomeCompetitions);
      }
      homeState.append(action);
      homeState.hidden = false;
    }
  }

  if (homeList) loadHomeCompetitions();

  if (!publicList) return;
  const count = document.getElementById('publicContestCount');
  const pagination = document.getElementById('publicContestPagination');
  const more = document.getElementById('loadMoreCompetitions');
  const tabs = document.querySelector('.public-contest-tabs');
  const params = new URLSearchParams(location.search);
  let status = ['available_now', 'ended'].includes(params.get('status')) ? params.get('status') : 'available_now';
  let page = 1;
  let lastPage = 1;
  let requestId = 0;

  const updateTabs = () => tabs.querySelectorAll('[data-filter]').forEach(button => {
    const active = button.dataset.filter === status;
    button.classList.toggle('active', active);
    button.setAttribute('aria-pressed', String(active));
  });

  async function loadList(append = false) {
    const currentRequest = ++requestId;
    const nextPage = append ? page + 1 : 1;
    if (!append) {
      page = 1;
      publicList.replaceChildren();
      listState('جارٍ تحميل المسابقات...');
      count.textContent = '—';
      pagination.hidden = true;
    } else {
      more.disabled = true;
      more.textContent = 'جارٍ التحميل...';
    }
    try {
      const result = await requestPage(status, nextPage, 10);
      if (currentRequest !== requestId) return;
      const items = result.data || [];
      if (!append) publicList.replaceChildren();
      else publicList.querySelector('.public-contest-empty')?.remove();
      items.forEach(item => publicList.append(publicCard(item)));
      page = Number(result.current_page || nextPage);
      lastPage = Number(result.last_page || 1);
      count.textContent = `${Number(result.total || 0).toLocaleString('ar-EG')} مسابقة`;
      if (!append && items.length === 0) {
        listState(status === 'available_now' ? 'لا توجد مسابقات متاحة الآن.' : 'لا توجد مسابقات منتهية حتى الآن.');
      }
      pagination.hidden = page >= lastPage;
    } catch (error) {
      const action = error.status === 401
        ? { label: 'تسجيل الدخول', href: '01-login.html' }
        : { label: 'إعادة المحاولة', onClick: () => loadList(append) };
      if (!append) stateCard(publicList, error.message || 'تعذر تحميل المسابقات.', action);
      else listState(error.message || 'تعذر تحميل المزيد من المسابقات.', action);
      pagination.hidden = true;
    } finally {
      more.disabled = false;
      more.textContent = 'عرض المزيد';
    }
  }

  function listState(message, action = null) {
    const state = document.createElement('div');
    state.className = 'public-contest-empty';
    const title = document.createElement('h3');
    title.textContent = message;
    state.append(title);
    if (action) {
      const button = action.href ? document.createElement('a') : document.createElement('button');
      button.className = 'contest-state-action';
      button.textContent = action.label;
      if (action.href) button.href = action.href;
      else {
        button.type = 'button';
        button.addEventListener('click', action.onClick);
      }
      state.append(button);
    }
    if (!publicList.children.length) publicList.replaceChildren(state);
    else publicList.append(state);
  }

  tabs.addEventListener('click', event => {
    const button = event.target.closest('[data-filter]');
    if (!button || button.dataset.filter === status) return;
    status = button.dataset.filter;
    params.set('status', status);
    history.replaceState(null, '', `${location.pathname}?${params}`);
    updateTabs();
    loadList();
  });
  more.addEventListener('click', () => loadList(true));
  updateTabs();
  loadList();
})();
