(() => {
  const productId = new URLSearchParams(location.search).get('product_id');
  const state = document.getElementById('detailsState');
  const gallery = document.getElementById('gallery');
  const content = document.querySelector('.details-content');
  const footer = document.querySelector('.details-bottom');
  const favorite = document.getElementById('favorite');
  const money = value => `${Number(value || 0).toLocaleString('ar-EG')} ج.م`;
  const origin = location.protocol === 'file:' ? 'http://127.0.0.1:8000' :
    ['localhost', '127.0.0.1'].includes(location.hostname) ? `${location.protocol}//${location.hostname}:8000` : location.origin;
  const token = () => localStorage.getItem('saey_api_token');
  let product;
  let quantity = 1;

  const showError = message => {
    state.replaceChildren();
    const text = document.createElement('span');
    text.textContent = message;
    const retry = document.createElement('button');
    retry.type = 'button';
    retry.textContent = 'إعادة المحاولة';
    retry.addEventListener('click', load);
    state.append(text, document.createElement('br'), retry);
    state.hidden = false;
  };
  const setFavorite = saved => {
    favorite.classList.toggle('saved', saved);
    favorite.setAttribute('aria-label', saved ? 'إزالة من المفضلة' : 'إضافة للمفضلة');
  };
  const setPolicy = policy => {
    const shortcut = document.getElementById('productPolicyShortcut');
    const card = document.getElementById('productPoliciesSection');
    if (!policy) { shortcut.hidden = true; card.hidden = true; return; }
    const summary = document.getElementById('productPolicies');
    summary.replaceChildren();
    const addRow = (title, detail) => {
      const row = document.createElement('div');
      row.className = 'policy-row';
      const body = document.createElement('div');
      const heading = document.createElement('strong');
      heading.textContent = title;
      const description = document.createElement('small');
      description.textContent = detail || '';
      body.append(heading, description);
      row.append(body);
      summary.append(row);
    };
    addRow(`استلام من المتجر خلال ${Number(policy.pickup_hold_hours).toLocaleString('ar-EG')} ساعة من الجاهزية`, policy.pickup_instructions);
    addRow(policy.returns_enabled ? `استرجاع خلال ${Number(policy.return_window_days).toLocaleString('ar-EG')} يومًا` : 'الاسترجاع غير متاح', policy.return_terms);
    if (policy.exchanges_enabled) addRow('الاستبدال متاح', policy.exchange_terms);
    if (policy.cancellation_enabled) addRow('إلغاء الطلب متاح', policy.cancellation_terms);
    document.getElementById('policyShortcutTitle').textContent = policy.returns_enabled ?
      `استرجاع خلال ${Number(policy.return_window_days).toLocaleString('ar-EG')} يومًا` : 'اطّلع على سياسات المتجر';
    document.getElementById('policyShortcutMeta').textContent = `حجز الطلب ${Number(policy.pickup_hold_hours).toLocaleString('ar-EG')} ساعة بعد الجاهزية`;
    shortcut.addEventListener('click', () => card.scrollIntoView({ behavior: 'smooth', block: 'center' }));
  };
  const setGallery = media => {
    const images = (media || []).filter(item => item.type === 'image' && item.url);
    const track = gallery.querySelector('.gallery-track');
    const dots = gallery.querySelector('.gallery-dots');
    track.replaceChildren();
    dots.replaceChildren();
    images.forEach((item, index) => {
      const image = document.createElement('img');
      image.src = item.url;
      image.alt = `${product.name} — صورة ${index + 1}`;
      track.append(image);
      const dot = document.createElement('button');
      dot.type = 'button';
      dot.setAttribute('aria-label', `الصورة ${index + 1}`);
      dot.addEventListener('click', () => show(index));
      dots.append(dot);
    });
    let slide = 0;
    const show = index => {
      slide = (index + images.length) % images.length;
      track.style.transform = `translateX(${-slide * 100}%)`;
      dots.querySelectorAll('button').forEach((dot, i) => dot.classList.toggle('active', i === slide));
    };
    if (images.length) show(0);
    gallery.querySelector('.gallery-arrow.next').hidden = images.length < 2;
    gallery.querySelector('.gallery-arrow.prev').hidden = images.length < 2;
    dots.hidden = images.length < 2;
    gallery.querySelector('.gallery-arrow.next').onclick = () => show(slide + 1);
    gallery.querySelector('.gallery-arrow.prev').onclick = () => show(slide - 1);
    gallery.hidden = images.length === 0;
  };
  const render = data => {
    product = data;
    setGallery(data.media);
    const discount = gallery.querySelector('.details-discount');
    discount.hidden = !Number(data.discount_percentage);
    discount.querySelector('span').textContent = Number(data.discount_percentage || 0).toLocaleString('ar-EG');
    content.querySelector('.details-store').textContent = data.category_name || '';
    content.querySelector('h1').textContent = data.name;
    content.querySelector('.details-rating b').textContent = data.average_rating == null ? '—' : Number(data.average_rating).toLocaleString('ar-EG');
    content.querySelector('.details-rating span').textContent = `(${Number(data.reviews_count).toLocaleString('ar-EG')} تقييم)`;
    content.querySelector('.details-prices strong').textContent = money(data.price);
    const original = content.querySelector('.details-prices del');
    original.hidden = !data.original_price;
    original.textContent = data.original_price ? money(data.original_price) : '';
    const saving = content.querySelector('.details-saving');
    const saved = Number(data.original_price || 0) - Number(data.price || 0);
    saving.hidden = saved <= 0;
    saving.textContent = saved > 0 ? `وفّرت ${money(saved)}` : '';
    const info = content.querySelectorAll('.details-quick-info span');
    info[0].replaceChildren();
    const categoryLabel = document.createElement('b');
    categoryLabel.textContent = 'القسم';
    info[0].append(categoryLabel, ` ${data.category_name || '—'}`);
    const created = data.created_at ? new Date(data.created_at).toLocaleDateString('ar-EG', { day: 'numeric', month: 'long', year: 'numeric' }) : '—';
    info[1].replaceChildren();
    const dateLabel = document.createElement('b');
    dateLabel.textContent = 'مضاف في';
    info[1].append(dateLabel, ` ${created}`);
    const features = content.querySelector('.product-features');
    features.replaceChildren();
    (data.features || []).forEach(feature => {
      const item = document.createElement('li');
      item.textContent = feature;
      features.append(item);
    });
    features.previousElementSibling.hidden = features.hidden = features.childElementCount === 0;
    content.querySelector('.details-description').textContent = data.description || 'لا يوجد وصف لهذا المنتج.';
    const seller = content.querySelector('.seller-card');
    const avatar = seller.querySelector('img');
    avatar.hidden = !data.store?.avatar;
    if (data.store?.avatar) avatar.src = data.store.avatar;
    seller.querySelector('strong').textContent = data.store?.name || '';
    seller.querySelector('span').textContent = 'متجر سعي';
    seller.querySelector('a').href = `19-store-details.html?store_id=${encodeURIComponent(data.store_id)}`;
    const call = content.querySelector('.seller-actions a[href^="tel:"]');
    const phone = `${data.store?.phone_code || ''}${data.store?.phone || ''}`;
    call.hidden = !phone;
    if (phone) call.href = `tel:${phone}`;
    content.querySelector('.seller-actions .chat-action').href = `14-chat-conversation.html?store_id=${encodeURIComponent(data.store_id)}`;
    setPolicy(data.policy);
    const reviews = content.querySelector('.ratings-section');
    reviews.querySelector('.review-summary strong').textContent = data.average_rating == null ? '— ★' : `${Number(data.average_rating).toLocaleString('ar-EG')} ★`;
    reviews.querySelector('.review-summary span').textContent = `بناءً على ${Number(data.reviews_count).toLocaleString('ar-EG')} تقييم`;
    const latest = reviews.querySelector('.review-card');
    latest.hidden = !data.latest_review;
    if (data.latest_review) {
      latest.querySelector('strong').textContent = `${data.latest_review.user_name || 'مستخدم سعي'} ★ ${data.latest_review.rating}`;
      latest.querySelector('p').textContent = data.latest_review.comment || '';
    }
    reviews.querySelector('.inline-section-head a').href = `25-product-reviews.html?product_id=${encodeURIComponent(data.id)}`;
    const similar = content.querySelector('.similar-products');
    similar.replaceChildren();
    (data.similar_products || []).forEach(item => {
      const link = document.createElement('a');
      link.href = `12-product-details.html?product_id=${encodeURIComponent(item.id)}`;
      const imageFrame = document.createElement('span');
      imageFrame.className = 'similar-product-image';
      if (item.image) {
        const image = document.createElement('img');
        image.src = item.image;
        image.alt = item.name;
        imageFrame.append(image);
      }
      if (item.average_rating != null) {
        const rating = document.createElement('span');
        rating.className = 'similar-product-rating';
        rating.textContent = `★ ${Number(item.average_rating).toLocaleString('ar-EG')}`;
        imageFrame.append(rating);
      }
      link.append(imageFrame);
      const name = document.createElement('strong');
      name.textContent = item.name;
      const price = document.createElement('span');
      price.textContent = money(item.price);
      link.append(name, price);
      similar.append(link);
    });
    similar.parentElement.hidden = similar.childElementCount === 0;
    setFavorite(Boolean(data.is_favorite));
    document.getElementById('buyNow').disabled = Number(data.stock_quantity) < 1;
    document.getElementById('buyNow').textContent = Number(data.stock_quantity) < 1 ? 'نفد المخزون' : 'شراء الآن';
    content.hidden = false;
    footer.hidden = false;
    state.hidden = true;
  };
  const load = async () => {
    if (!productId || !/^\d+$/.test(productId)) { showError('افتح المنتج من قائمة المنتجات لعرض تفاصيله.'); return; }
    state.textContent = 'جارٍ تحميل تفاصيل المنتج...';
    state.hidden = false;
    try {
      const response = await fetch(`${origin}/api/user/products/${encodeURIComponent(productId)}`, {
        headers: { Accept: 'application/json', 'Accept-Language': 'ar', ...(token() ? { Authorization: `Bearer ${token()}` } : {}) }
      });
      const payload = await response.json();
      if (!response.ok) throw new Error(response.status === 401 ? 'سجّل الدخول لعرض تفاصيل المنتج.' : response.status === 404 ? 'المنتج غير متاح حاليًا.' : (payload.message || 'تعذر تحميل المنتج.'));
      render(payload.data);
    } catch (error) { showError(error.message || 'تعذر تحميل المنتج.'); }
  };
  favorite.addEventListener('click', async () => {
    if (!product) return;
    favorite.disabled = true;
    try {
      const response = await fetch(`${origin}/api/user/favorites/toggle`, {
        method: 'POST',
        headers: { Accept: 'application/json', 'Accept-Language': 'ar', 'Content-Type': 'application/json', ...(token() ? { Authorization: `Bearer ${token()}` } : {}) },
        body: JSON.stringify({ type: 'product', favoriteable_id: product.id })
      });
      const payload = await response.json();
      if (!response.ok) throw new Error(payload.message || 'تعذر تحديث المفضلة.');
      setFavorite(Boolean(payload.data?.is_favorite));
    } catch (error) { showError(error.message || 'تعذر تحديث المفضلة.'); }
    finally { favorite.disabled = false; }
  });
  document.getElementById('plus').onclick = () => {
    quantity = Math.min(Number(product?.stock_quantity || 1), quantity + 1);
    document.getElementById('qty').textContent = quantity;
  };
  document.getElementById('minus').onclick = () => {
    quantity = Math.max(1, quantity - 1);
    document.getElementById('qty').textContent = quantity;
  };
  document.getElementById('buyNow').onclick = () => {
    if (product) location.href = `20-checkout.html?product_id=${encodeURIComponent(product.id)}&qty=${quantity}`;
  };
  document.getElementById('share').onclick = async () => {
    if (product && navigator.share) await navigator.share({ title: product.name, url: location.href }).catch(() => {});
  };
  const reportDialog = document.getElementById('reportDialog');
  document.getElementById('report').onclick = () => reportDialog.showModal();
  load();
})();
