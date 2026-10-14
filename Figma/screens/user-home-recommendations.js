(() => {
  const list = document.getElementById('recommendedProducts');
  if (!list) return;

  const apiOrigin = location.protocol === 'file:'
    ? 'http://127.0.0.1:8000'
    : ['localhost', '127.0.0.1'].includes(location.hostname)
      ? `${location.protocol}//${location.hostname}:8000`
      : location.origin;
  const token = () => localStorage.getItem('saey_api_token');
  const money = value => `${Number(value || 0).toLocaleString('ar-EG')} ج.م`;

  const showState = (message, action = null) => {
    list.replaceChildren();
    const state = document.createElement('div');
    state.className = 'recommended-products-state';
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

  const productCard = product => {
    const card = document.createElement('a');
    card.className = 'waffarha-product';
    card.href = `12-product-details.html?product_id=${encodeURIComponent(product.id)}`;

    const photo = document.createElement('span');
    photo.className = 'product-photo';
    if (product.image) {
      const image = document.createElement('img');
      image.src = product.image;
      image.alt = product.name;
      image.loading = 'lazy';
      photo.append(image);
    } else {
      const placeholder = document.createElement('span');
      placeholder.className = 'recommended-product-placeholder';
      placeholder.textContent = String(product.name || 'س').trim().charAt(0) || 'س';
      photo.append(placeholder);
    }
    if (Number(product.discount_percentage) > 0) {
      const discount = document.createElement('em');
      discount.textContent = `خصم ${Number(product.discount_percentage).toLocaleString('ar-EG')}٪`;
      photo.append(discount);
    }

    const name = document.createElement('strong');
    name.textContent = product.name;
    const category = document.createElement('small');
    category.textContent = product.category_name || 'منتج مقترح';
    const price = document.createElement('b');
    price.textContent = money(product.price);
    const rating = document.createElement('span');
    rating.className = 'rating';
    rating.setAttribute('aria-label', `التقييم ${product.average_rating || 0} من 5`);
    rating.innerHTML = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m12 3 2.7 5.5 6.1.9-4.4 4.3 1 6.1-5.4-2.9-5.4 2.9 1-6.1-4.4-4.3 6.1-.9Z"/></svg>';
    rating.append(document.createTextNode(` ${Number(product.average_rating || 0).toLocaleString('ar-EG', { maximumFractionDigits: 1 })}`));

    card.append(photo, name, category, price, rating);
    return card;
  };

  async function load() {
    showState('جارٍ تجهيز اختياراتك...');
    try {
      const response = await fetch(`${apiOrigin}/api/user/products/recommended?limit_per_page=6`, {
        headers: {
          Accept: 'application/json',
          'Accept-Language': 'ar',
          ...(token() ? { Authorization: `Bearer ${token()}` } : {})
        }
      });
      const payload = await response.json();
      if (response.status === 401) {
        showState('سجّل الدخول لعرض اختيارات تناسبك.', { label: 'تسجيل الدخول', href: '01-login.html' });
        return;
      }
      if (!response.ok) throw new Error(payload.message || 'تعذر تحميل المنتجات المقترحة.');
      list.replaceChildren();
      if (!payload.data?.length) {
        showState('لا توجد منتجات مقترحة متاحة حاليًا.');
        return;
      }
      payload.data.forEach(product => list.append(productCard(product)));
    } catch (error) {
      showState(error.message || 'تعذر تحميل المنتجات المقترحة.', { label: 'إعادة المحاولة' });
    }
  }

  load();
})();
