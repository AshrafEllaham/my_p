(() => {
  const params = new URLSearchParams(location.search);
  const input = document.getElementById('searchQuery');
  const results = document.getElementById('results');
  const empty = document.getElementById('empty');
  const loading = document.getElementById('searchLoading');
  const error = document.getElementById('searchError');
  const retry = document.getElementById('retrySearch');
  const more = document.getElementById('loadMoreProducts');
  const origin = location.protocol === 'file:' ? 'http://127.0.0.1:8000' :
    ['localhost', '127.0.0.1'].includes(location.hostname) ? `${location.protocol}//${location.hostname}:8000` : location.origin;
  input.value = params.get('search') || params.get('q') || '';
  const similarTo = params.get('similar_to');
  if (similarTo) document.querySelector('.flow-head h1').textContent = 'منتجات مشابهة';
  let timer;
  let controller;
  let page = 1;

  const load = async (append = false) => {
    controller?.abort();
    controller = new AbortController();
    const activeController = controller;
    if (!append) { page = 1; results.replaceChildren(); }
    more.hidden = true;
    results.hidden = !append;
    loading.hidden = false;
    empty.hidden = true;
    error.hidden = true;
    const query = new URLSearchParams({ pagination: 'on', limit_per_page: '20', page: String(page) });
    ['main_category_id', 'sub_category_id', 'governorate_id', 'sort'].forEach(key => {
      if (params.get(key)) query.set(key, params.get(key));
    });
    if (input.value.trim()) query.set('search', input.value.trim());
    try {
      const token = localStorage.getItem('saey_api_token');
      const endpoint = similarTo ? `products/${encodeURIComponent(similarTo)}/similar-products` : 'products';
      const response = await fetch(`${origin}/api/user/${endpoint}?${query}`, {
        signal: controller.signal,
        headers: { Accept: 'application/json', 'Accept-Language': 'ar', ...(token ? { Authorization: `Bearer ${token}` } : {}) }
      });
      const payload = await response.json();
      if (!response.ok) throw new Error(response.status === 401 ? 'سجّل الدخول لعرض المنتجات.' : (payload.message || 'تعذر تحميل المنتجات.'));
      (payload.data || []).forEach(product => {
        const card = document.createElement('a');
        card.className = 'result-item';
        card.href = `12-product-details.html?product_id=${encodeURIComponent(product.id)}`;
        if (product.image) {
          const image = document.createElement('img');
          image.src = product.image;
          image.alt = product.name || '';
          card.append(image);
        }
        const details = document.createElement('div');
        const name = document.createElement('strong');
        name.textContent = product.name || '';
        const category = document.createElement('small');
        category.textContent = product.category_name || '';
        const price = document.createElement('b');
        price.textContent = `${Number(product.price || 0).toLocaleString('ar-EG')} ج.م`;
        details.append(name, category, price);
        card.append(details);
        results.append(card);
      });
      empty.hidden = results.childElementCount > 0;
      results.hidden = false;
      page = Number(payload.current_page || page) + 1;
      more.hidden = page > Number(payload.last_page || 1);
    } catch (cause) {
      if (cause.name === 'AbortError') return;
      error.querySelector('strong').textContent = cause.message || 'تعذر تحميل نتائج البحث.';
      error.hidden = false;
    } finally {
      if (controller === activeController) loading.hidden = true;
    }
  };
  input.addEventListener('input', () => {
    clearTimeout(timer);
    timer = setTimeout(() => load(), 300);
  });
  retry.addEventListener('click', () => load());
  more.addEventListener('click', () => load(true));
  load();
})();
