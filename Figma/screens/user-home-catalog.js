(() => {
  const sections = Array.from(document.querySelectorAll('.collection-section'));
  if (!sections.length) return;
  const origin = location.protocol === 'file:' ? 'http://127.0.0.1:8000' :
    ['localhost', '127.0.0.1'].includes(location.hostname) ? `${location.protocol}//${location.hostname}:8000` : location.origin;
  const token = localStorage.getItem('saey_api_token');
  const aliases = [
    ['ملابس', 'أزياء', 'fashion', 'clothing'],
    ['إلكترون', 'هواتف', 'تقنية', 'electronics', 'technology'],
    ['منزل', 'مفروشات', 'أثاث', 'home', 'furniture']
  ];
  const empty = message => {
    sections.forEach(section => {
      section.querySelector('.waffarha-product-row')?.replaceChildren();
      const state = document.createElement('p');
      state.className = 'home-catalog-state';
      state.textContent = message;
      section.querySelector('.waffarha-product-row')?.append(state);
    });
  };
  const productCard = product => {
    const card = document.createElement('a');
    card.className = 'waffarha-product';
    card.href = `12-product-details.html?product_id=${encodeURIComponent(product.id)}`;
    const photo = document.createElement('span');
    photo.className = 'product-photo';
    if (product.image) {
      const image = document.createElement('img');
      image.src = product.image;
      image.alt = product.name || '';
      image.loading = 'lazy';
      photo.append(image);
    }
    const name = document.createElement('strong');
    name.textContent = product.name || '';
    const category = document.createElement('small');
    category.textContent = product.category_name || '';
    const price = document.createElement('b');
    price.textContent = `${Number(product.price || 0).toLocaleString('ar-EG')} ج.م`;
    const rating = document.createElement('span');
    rating.className = 'rating';
    rating.textContent = `★ ${Number(product.average_rating || 0).toLocaleString('ar-EG', { maximumFractionDigits: 1 })}`;
    card.append(photo, name, category, price, rating);
    return card;
  };
  const load = async () => {
    empty(token ? 'جارٍ تحميل المنتجات…' : 'سجّل الدخول لعرض المنتجات.');
    if (!token) return;
    try {
      const response = await fetch(`${origin}/api/main-categories?pagination=off`, { headers: { Accept: 'application/json', 'Accept-Language': 'ar' } });
      const categoryPayload = await response.json();
      if (!response.ok) throw new Error(categoryPayload.message || 'تعذر تحميل الأقسام.');
      for (const [index, section] of sections.entries()) {
        const row = section.querySelector('.waffarha-product-row');
        if (!row) continue;
        const candidates = aliases[index] || [];
        const category = (categoryPayload.data || []).find(item => candidates.some(alias => String(item.name || '').toLocaleLowerCase().includes(alias.toLocaleLowerCase())));
        if (!category) {
          section.hidden = true;
          continue;
        }
        section.hidden = false;
        const query = new URLSearchParams({ main_category_id: String(category.id), sort: 'most_purchased', limit_per_page: '3' });
        const productsResponse = await fetch(`${origin}/api/user/products?${query}`, { headers: { Accept: 'application/json', 'Accept-Language': 'ar', Authorization: `Bearer ${token}` } });
        const productsPayload = await productsResponse.json();
        if (!productsResponse.ok) throw new Error(productsPayload.message || 'تعذر تحميل منتجات القسم.');
        row.replaceChildren();
        (productsPayload.data || []).forEach(product => row.append(productCard(product)));
        if (!productsPayload.data?.length) {
          const state = document.createElement('p');
          state.className = 'home-catalog-state';
          state.textContent = 'لا توجد منتجات متاحة في هذا القسم حاليًا.';
          row.append(state);
        }
        const viewAll = section.querySelector('.section-action');
        if (viewAll) viewAll.href = `10-featured-products.html?main_category_id=${encodeURIComponent(category.id)}`;
      }
    } catch (error) {
      empty(error.message || 'تعذر تحميل منتجات الأقسام.');
    }
  };
  load();
})();
