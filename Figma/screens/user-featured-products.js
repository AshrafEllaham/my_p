(() => {
  const grid = document.getElementById('featuredProducts');
  if (!grid) return;
  const params = new URLSearchParams(location.search);
  const input = document.querySelector('.catalog-search input');
  const subcategories = document.querySelector('.filter-scroller');
  const governorates = document.getElementById('featuredGovernorate');
  const label = document.getElementById('featuredResultLabel');
  const message = document.getElementById('featuredMessage');
  const more = document.getElementById('featuredLoadMore');
  const token = localStorage.getItem('saey_api_token');
  const origin = location.protocol === 'file:' ? 'http://127.0.0.1:8000' :
    ['localhost', '127.0.0.1'].includes(location.hostname) ? `${location.protocol}//${location.hostname}:8000` : location.origin;
  let page = 1;
  let lastPage = 1;
  let selectedSubcategory = params.get('sub_category_id') || '';
  let timer;

  const api = async (path, method = 'GET', body = null) => {
    const response = await fetch(`${origin}/api/${path}`, {
      method,
      headers: {
        Accept: 'application/json',
        'Accept-Language': 'ar',
        ...(token ? { Authorization: `Bearer ${token}` } : {}),
        ...(body ? { 'Content-Type': 'application/json' } : {})
      },
      ...(body ? { body: JSON.stringify(body) } : {})
    });
    const result = await response.json();
    if (!response.ok) throw new Error(Object.values(result.errors || {}).flat()[0] || result.message || 'تعذر تحميل المنتجات.');
    return result;
  };
  const setMessage = text => { message.textContent = text || ''; };
  const makeOption = (select, row) => select.add(new Option(row.name, String(row.id), false, String(row.id) === String(params.get('governorate_id') || '')));
  const makeCard = product => {
    const card = document.createElement('a');
    card.className = 'catalog-product';
    card.href = `12-product-details.html?product_id=${encodeURIComponent(product.id)}`;
    const imageWrap = document.createElement('span');
    imageWrap.className = 'catalog-product-image';
    if (product.image) {
      const image = document.createElement('img');
      image.src = product.image;
      image.alt = product.name || '';
      image.loading = 'lazy';
      imageWrap.append(image);
    }
    if (Number(product.discount_percentage) > 0) {
      const discount = document.createElement('em');
      discount.className = 'discount-ribbon';
      discount.textContent = `${Number(product.discount_percentage)}%`;
      imageWrap.append(discount);
    }
    const favorite = document.createElement('button');
    favorite.type = 'button';
    favorite.setAttribute('aria-label', 'إضافة إلى المفضلة');
    favorite.classList.toggle('saved', Boolean(product.is_favorite));
    favorite.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/></svg>';
    favorite.addEventListener('click', async event => {
      event.preventDefault();
      event.stopPropagation();
      favorite.disabled = true;
      try {
        const result = await api('user/favorites/toggle', 'POST', { type: 'product', favoriteable_id: product.id });
        product.is_favorite = result.data?.is_favorite ?? !product.is_favorite;
        favorite.classList.toggle('saved', Boolean(product.is_favorite));
        favorite.setAttribute('aria-label', product.is_favorite ? 'إزالة من المفضلة' : 'إضافة إلى المفضلة');
        setMessage(result.message || 'تم تحديث المفضلة.');
      } catch (error) {
        setMessage(error.message);
      } finally {
        favorite.disabled = false;
      }
    });
    imageWrap.append(favorite);
    const category = document.createElement('span');
    category.className = 'catalog-product-category';
    category.textContent = product.category_name || product.store_name || '';
    const name = document.createElement('strong');
    name.textContent = product.name || '';
    const price = document.createElement('span');
    price.className = 'catalog-price';
    if (product.original_price && Number(product.original_price) > Number(product.price)) {
      const oldPrice = document.createElement('del');
      oldPrice.textContent = `${Number(product.original_price).toLocaleString('ar-EG')} ج.م`;
      price.append(oldPrice);
    }
    const current = document.createElement('b');
    current.textContent = `${Number(product.price || 0).toLocaleString('ar-EG')} ج.م`;
    price.append(current);
    const rating = document.createElement('span');
    rating.className = 'catalog-rating';
    rating.textContent = `${Number(product.average_rating || 0).toLocaleString('ar-EG')} ★`;
    card.append(imageWrap, category, name, price, rating);
    return card;
  };
  const load = async (append = false) => {
    if (!append) {
      page = 1;
      grid.replaceChildren();
    }
    grid.setAttribute('aria-busy', 'true');
    more.hidden = true;
    setMessage(token ? 'جارٍ تحميل المنتجات…' : 'سجّل الدخول لعرض المنتجات.');
    label.textContent = 'جارٍ تحميل المنتجات…';
    if (!token) {
      grid.setAttribute('aria-busy', 'false');
      label.textContent = 'يتطلب عرض المنتجات تسجيل الدخول';
      setMessage('سجّل الدخول لعرض المنتجات وإدارة المفضلة.');
      return;
    }
    const query = new URLSearchParams({ pagination: 'on', limit_per_page: '20', page: String(page), sort: document.querySelector('.sort-option.selected')?.dataset.sort || 'highest_rated' });
    if (params.get('main_category_id')) query.set('main_category_id', params.get('main_category_id'));
    if (selectedSubcategory) query.set('sub_category_id', selectedSubcategory);
    if (governorates.value) query.set('governorate_id', governorates.value);
    if (input.value.trim()) query.set('search', input.value.trim());
    try {
      const result = await api(`user/products?${query}`);
      (result.data || []).forEach(product => grid.append(makeCard(product)));
      page = Number(result.current_page || page) + 1;
      lastPage = Number(result.last_page || 1);
      label.textContent = result.total ? `${Number(result.total).toLocaleString('ar-EG')} منتج` : 'المنتجات المتاحة';
      setMessage((result.data || []).length ? '' : 'لا توجد منتجات مطابقة للفلاتر المختارة.');
      more.hidden = page > lastPage;
    } catch (error) {
      setMessage(error.message || 'تعذر تحميل المنتجات.');
      label.textContent = 'تعذر تحميل المنتجات';
      if (grid.childElementCount === 0) {
        const retry = document.createElement('button');
        retry.type = 'button';
        retry.className = 'apply-filters';
        retry.textContent = 'إعادة المحاولة';
        retry.addEventListener('click', () => load());
        grid.append(retry);
      }
    } finally {
      grid.setAttribute('aria-busy', 'false');
    }
  };

  const initializeFilters = async () => {
    subcategories.replaceChildren();
    const all = document.createElement('button');
    all.type = 'button';
    all.className = `filter-chip${selectedSubcategory ? '' : ' selected'}`;
    all.dataset.subcategoryId = '';
    all.textContent = 'الكل';
    subcategories.append(all);
    try {
      const [subResult, governorateResult] = await Promise.all([
        api(`sub-categories?${params.get('main_category_id') ? `main_category_id=${encodeURIComponent(params.get('main_category_id'))}&` : ''}pagination=off`),
        api('governorates?pagination=off')
      ]);
      (subResult.data || []).forEach(item => {
        const option = document.createElement('button');
        option.type = 'button';
        option.className = `filter-chip${String(item.id) === String(selectedSubcategory) ? ' selected' : ''}`;
        option.dataset.subcategoryId = String(item.id);
        option.textContent = item.name || '';
        subcategories.append(option);
      });
      (governorateResult.data || []).forEach(makeOption.bind(null, governorates));
    } catch (error) {
      setMessage(error.message);
    }
  };

  subcategories.addEventListener('click', event => {
    const option = event.target.closest('.filter-chip');
    if (!option) return;
    subcategories.querySelectorAll('.filter-chip').forEach(item => item.classList.toggle('selected', item === option));
    selectedSubcategory = option.dataset.subcategoryId || '';
  });
  document.querySelector('.apply-filters').addEventListener('click', () => load());
  more.addEventListener('click', () => load(true));
  input.addEventListener('input', () => { clearTimeout(timer); timer = setTimeout(() => load(), 300); });
  initializeFilters().finally(() => load());
})();
