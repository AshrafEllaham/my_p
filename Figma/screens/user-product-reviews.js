(() => {
  const params = new URLSearchParams(location.search);
  const productId = params.get('product_id') || (params.get('design_mode') === 'static' ? '101' : null);
  const origin = location.protocol === 'file:' ? 'http://127.0.0.1:8000' :
    ['localhost', '127.0.0.1'].includes(location.hostname) ? `${location.protocol}//${location.hostname}:8000` : location.origin;
  const status = document.getElementById('reviewsStatus');
  const list = document.getElementById('reviewsList');
  const summary = document.getElementById('reviewsSummary');
  const rating = document.getElementById('reviewsRating');
  const more = document.getElementById('reviewsMore');
  const retry = document.getElementById('reviewsRetry');
  const back = document.getElementById('reviewsBack');
  if (productId) back.href = `12-product-details.html?product_id=${encodeURIComponent(productId)}`;
  let page = 1;
  let controller;

  const load = async (append = false) => {
    if (!productId || !/^\d+$/.test(productId)) {
      status.textContent = 'افتح التقييمات من صفحة المنتج.';
      return;
    }
    controller?.abort();
    controller = new AbortController();
    const active = controller;
    if (!append) { page = 1; list.replaceChildren(); }
    status.textContent = 'جارٍ تحميل التقييمات...';
    status.hidden = false;
    more.hidden = true;
    retry.hidden = true;
    const query = new URLSearchParams({ page: String(page), limit_per_page: '10' });
    if (rating.value) query.set('rating', rating.value);
    try {
      const token = localStorage.getItem('saey_api_token');
      const response = await fetch(`${origin}/api/user/products/${encodeURIComponent(productId)}/reviews?${query}`, {
        signal: active.signal,
        headers: { Accept: 'application/json', 'Accept-Language': 'ar', ...(token ? { Authorization: `Bearer ${token}` } : {}) }
      });
      const payload = await response.json();
      if (!response.ok) throw new Error(response.status === 401 ? 'سجّل الدخول لعرض التقييمات.' : (payload.message || 'تعذر تحميل التقييمات.'));
      const average = payload.summary?.average_rating;
      document.getElementById('reviewsAverage').textContent = average == null ? '—' : Number(average).toLocaleString('ar-EG', { maximumFractionDigits: 2 });
      document.getElementById('reviewsCount').textContent = `بناءً على ${Number(payload.summary?.reviews_count || 0).toLocaleString('ar-EG')} تقييم موثق`;
      summary.hidden = false;
      (payload.data || []).forEach(review => {
        const card = document.createElement('article');
        card.className = 'review-item';
        const header = document.createElement('header');
        const name = document.createElement('strong');
        name.textContent = review.user?.name || 'مستخدم سعي';
        const stars = document.createElement('b');
        stars.textContent = `★ ${Number(review.rating).toLocaleString('ar-EG')}`;
        header.append(name, stars);
        const comment = document.createElement('p');
        comment.textContent = review.comment || '';
        const date = document.createElement('time');
        date.dateTime = review.created_at || '';
        date.textContent = review.created_at ? new Date(review.created_at).toLocaleDateString('ar-EG', { day: 'numeric', month: 'long', year: 'numeric' }) : '';
        card.append(header, comment, date);
        list.append(card);
      });
      status.hidden = list.childElementCount > 0;
      if (!status.hidden) status.textContent = 'لا توجد تقييمات مطابقة.';
      page = Number(payload.current_page || page) + 1;
      more.hidden = page > Number(payload.last_page || 1);
    } catch (error) {
      if (error.name === 'AbortError') return;
      status.textContent = error.message || 'تعذر تحميل التقييمات.';
      status.hidden = false;
      retry.hidden = false;
    }
  };
  rating.addEventListener('change', () => load());
  more.addEventListener('click', () => load(true));
  retry.addEventListener('click', () => load());
  load();
})();
