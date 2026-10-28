/*
 * Offline-only fixtures for the Figma screen preview.
 * Enabled by preview.html with ?design_mode=static; app flows outside the
 * preview continue to use their configured API.
 */
(() => {
  const params = new URLSearchParams(location.search);
  const previewSessionName = '__SAEY_FIGMA_PREVIEW_STATIC__';
  const isPreviewFrame = window.parent !== window && window.frameElement?.id === 'phoneViewportIframe';
  const isPreviewSession = isPreviewFrame || window.name === previewSessionName;
  if (params.get('design_mode') !== 'static' && isPreviewSession) {
    params.set('design_mode', 'static');
    history.replaceState(null, '', `${location.pathname}?${params.toString()}${location.hash}`);
  }
  if (params.get('design_mode') !== 'static') return;
  window.name = previewSessionName;

  const storage = new Map([
    ['saey_api_token', 'preview.user.eyJzdWIiOiIxIn0.preview'],
    ['saey_api_base', '/api'],
    ['saey_user', JSON.stringify({ id: 1, name: 'مريم أحمد', phone: '01012345678' })],
    ['saey_api_user', JSON.stringify({ id: 1, name: 'مريم أحمد', phone: '01012345678' })],
    ['saey_signup_draft', JSON.stringify({ userId: 1, phone_code: '+20', phone: '1012345678' })],
    ['saey_wallet_balance', '2500'],
    ['saey_order_disputes_v1', JSON.stringify([{ id: 'DSP-601', order: 'SA-1014', note: 'المنتج المستلم لا يطابق الوصف والصور المعروضة.', status: 'under_review', date: '2026-10-28T09:30:00Z' }])]
  ]);
  const storageArea = {
    get length() { return storage.size; },
    key(index) { return [...storage.keys()][index] ?? null; },
    getItem(key) { return storage.has(String(key)) ? storage.get(String(key)) : null; },
    setItem(key, value) { storage.set(String(key), String(value)); },
    removeItem(key) { storage.delete(String(key)); },
    clear() { storage.clear(); }
  };
  try {
    Object.defineProperty(window, 'localStorage', { configurable: true, value: storageArea });
    Object.defineProperty(window, 'sessionStorage', { configurable: true, value: storageArea });
  } catch (_) {}

  const keepPreviewModeOnLinks = () => {
    document.querySelectorAll('a[href]').forEach(anchor => {
      const raw = anchor.getAttribute('href');
      if (!raw || /^(?:[a-z]+:|\/\/|#|mailto:|tel:|javascript:)/i.test(raw)) return;
      try {
        const target = new URL(raw, location.href);
        if (target.origin !== location.origin || !/\.html$/i.test(target.pathname)) return;
        target.searchParams.set('design_mode', 'static');
        target.searchParams.set('lang', document.documentElement.lang === 'en' ? 'en' : 'ar');
        target.searchParams.set('theme', document.body.classList.contains('dark-mode') || document.documentElement.dataset.theme === 'dark' ? 'dark' : 'light');
        anchor.setAttribute('href', `${target.pathname.split('/').pop()}${target.search}${target.hash}`);
      } catch (_) {}
    });
  };
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', keepPreviewModeOnLinks, { once: true });
  } else {
    keepPreviewModeOnLinks();
  }
  document.addEventListener('click', event => {
    const anchor = event.target.closest?.('a[href]');
    if (!anchor) return;
    keepPreviewModeOnLinks();
  }, true);
  new MutationObserver(keepPreviewModeOnLinks).observe(document.documentElement, { childList: true, subtree: true });

  const products = [
    { id: 101, name: 'فستان أنيق بتصميم عصري', category_name: 'أزياء', store_name: 'أزياء الشرق', image: 'https://images.unsplash.com/photo-1496747611176-843222e1e57c?auto=format&fit=crop&w=520&q=85', price: 1890, original_price: 2520, discount_percentage: 25, average_rating: 4.8, reviews_count: 36, stock_quantity: 12, is_favorite: true, description: 'خامة مريحة وقصة أنيقة تناسب إطلالتك اليومية.', images: [
      'https://images.unsplash.com/photo-1496747611176-843222e1e57c?auto=format&fit=crop&w=800&q=85',
      'https://images.unsplash.com/photo-1539109136881-3be0616acf4b?auto=format&fit=crop&w=800&q=85',
      'https://images.unsplash.com/photo-1595777457583-95e059d581b8?auto=format&fit=crop&w=800&q=85'
    ] },
    { id: 102, name: 'حذاء رياضي يومي', category_name: 'أزياء', store_name: 'سبورت لاين', image: 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=520&q=85', price: 950, original_price: 1100, discount_percentage: 14, average_rating: 4.7, reviews_count: 24, stock_quantity: 8, is_favorite: false, description: 'حذاء خفيف ومريح للاستخدام اليومي.', images: [] },
    { id: 103, name: 'حقيبة عملية', category_name: 'أزياء', store_name: 'ستايل هب', image: 'https://images.unsplash.com/photo-1523381294911-8d3cead13475?auto=format&fit=crop&w=520&q=85', price: 1190, original_price: 1190, discount_percentage: 0, average_rating: 4.8, reviews_count: 18, stock_quantity: 15, is_favorite: false, description: 'تصميم عملي ومساحة واسعة للأغراض اليومية.', images: [] },
    { id: 104, name: 'هاتف ذكي', category_name: 'إلكترونيات', store_name: 'موبايل بوينت', image: 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?auto=format&fit=crop&w=520&q=85', price: 15500, original_price: 16900, discount_percentage: 8, average_rating: 4.8, reviews_count: 52, stock_quantity: 6, is_favorite: false, description: 'هاتف ذكي بمواصفات مناسبة للاستخدام اليومي.', images: [] },
    { id: 105, name: 'لابتوب للعمل والدراسة', category_name: 'إلكترونيات', store_name: 'التقنية الحديثة', image: 'https://images.unsplash.com/photo-1498049794561-7780e7231661?auto=format&fit=crop&w=520&q=85', price: 24900, original_price: 24900, discount_percentage: 0, average_rating: 4.9, reviews_count: 31, stock_quantity: 4, is_favorite: false, description: 'أداء موثوق للعمل والدراسة وإنجاز المهام اليومية.', images: [] },
    { id: 106, name: 'كنبة عصرية مريحة', category_name: 'المنزل', store_name: 'بيتك أجمل', image: 'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?auto=format&fit=crop&w=520&q=85', price: 8950, original_price: 9600, discount_percentage: 7, average_rating: 4.8, reviews_count: 17, stock_quantity: 3, is_favorite: false, description: 'كنبة مريحة بتصميم هادئ يناسب المنزل العصري.', images: [] }
  ];
  const stores = [
    { id: 201, name: 'أزياء الشرق', category_name: 'أزياء وملابس', image: products[0].image, cover: products[0].image, average_rating: 4.8, reviews_count: 36, distance_km: 1.2, products_count: 42, description: 'اختيارات أنيقة لكل يوم.' },
    { id: 202, name: 'موبايل بوينت', category_name: 'إلكترونيات', image: products[3].image, cover: products[3].image, average_rating: 4.7, reviews_count: 52, distance_km: 2.4, products_count: 28, description: 'أجهزة وإكسسوارات أصلية.' },
    { id: 203, name: 'بيتك أجمل', category_name: 'المنزل والمفروشات', image: products[5].image, cover: products[5].image, average_rating: 4.9, reviews_count: 17, distance_km: 3.1, products_count: 19, description: 'تفاصيل جميلة لمنزلك.' }
  ];
  const categories = [
    { id: 1, name: 'ملابس وأزياء', image: products[0].image, icon: 'fashion' },
    { id: 2, name: 'إلكترونيات', image: products[3].image, icon: 'electronics' },
    { id: 3, name: 'المنزل والمفروشات', image: products[5].image, icon: 'home' },
    { id: 4, name: 'الجمال والعناية', image: products[2].image, icon: 'beauty' }
  ];
  const banners = [{ id: 1, title: 'عروض سعي', image: 'https://images.unsplash.com/photo-1607082349566-187342175e2f?auto=format&fit=crop&w=1000&q=85', link: '10-featured-products.html' }];
  const ads = [{ id: 301, title: 'اكتشف اختيارات الأسبوع', description: 'عروض مميزة من متاجر قريبة منك', image: products[4].image, media_url: products[4].image, media_type: 'image', video_url: '', action: 'view_store', store: stores[1], store_name: stores[1].name, product: products[3], product_id: products[3].id, is_favorite: false }];
  const competitions = [{ id: 401, title: 'اربح مع أزياء الشرق', question: 'ما القطعة التي تفضلها لإطلالة صيفية مميزة؟', description: 'أجب عن السؤال وادخل السحب على جائزة مميزة', image: products[0].image, prize: 'قسيمة شراء بقيمة ١٬٠٠٠ ج.م', prize_title: 'قسيمة شراء بقيمة ١٬٠٠٠ ج.م', end_at: '2026-12-31T20:00:00Z', ends_at: '2026-12-31T20:00:00Z', entries_count: 84, status: 'available_now', store: stores[0] }];
  const notifications = [
    { id: 501, title: 'عرض جديد يناسبك', message: 'اكتشف أحدث المنتجات والعروض من متاجرك المفضلة.', created_at: '2026-10-28T09:30:00Z', is_read: false, action_url: '10-featured-products.html' },
    { id: 502, title: 'تحديث حالة طلبك', message: 'طلبك قيد التجهيز لدى المتجر.', created_at: '2026-10-27T16:15:00Z', is_read: true, action_url: '16-orders.html' }
  ];
  const orders = [{ id: 601, number: 'SAE-2026-0108', status: 'preparing', total: 1890, total_amount: 1890, created_at: '2026-10-27T14:00:00Z', store: stores[0], store_name: stores[0].name, items: [{ product: products[0], product_name: products[0].name, quantity: 1, price: 1890 }] }];
  const transactions = [{ id: 701, type: 'deposit', title: 'إضافة رصيد', description: 'إضافة رصيد إلى محفظتك', amount: 2500, currency: 'EGP', balance_before: 0, balance_after: 2500, created_at: '2026-10-26T12:00:00Z', status: 'completed' }, { id: 702, type: 'purchase', title: 'شراء منتج', description: products[0].name, amount: 1890, currency: 'EGP', balance_before: 2500, balance_after: 610, created_at: '2026-10-27T14:00:00Z', status: 'completed' }, { id: 703, type: 'refund', title: 'استرداد', description: 'استرداد طلب', amount: 320, currency: 'EGP', balance_before: 610, balance_after: 930, created_at: '2026-10-28T09:00:00Z', status: 'completed' }];

  const jsonResponse = (data, status = 200, extra = {}) => new Response(JSON.stringify({ data, current_page: 1, last_page: 1, per_page: Array.isArray(data) ? data.length : 1, total: Array.isArray(data) ? data.length : 1, message: 'تم بنجاح', ...extra }), { status, headers: { 'Content-Type': 'application/json; charset=utf-8' } });
  const list = (items, url) => {
    const query = new URL(url).searchParams;
    const search = (query.get('search') || '').toLocaleLowerCase();
    const filtered = search ? items.filter(item => `${item.name || ''} ${item.title || ''} ${item.category_name || ''}`.toLocaleLowerCase().includes(search)) : items;
    return filtered;
  };
  window.fetch = async (input, init = {}) => {
    const url = input instanceof Request ? input.url : String(input);
    let parsed;
    try { parsed = new URL(url, location.href); } catch (_) { return jsonResponse({}); }
    const path = parsed.pathname.replace(/\/+$/, '') || '/';
    const lower = path.toLowerCase();
    const method = String(init.method || (input instanceof Request ? input.method : 'GET')).toUpperCase();
    const matchId = lower.match(/\/(\d+)(?:\/|$)/);
    const id = Number(matchId?.[1] || parsed.searchParams.get('product_id') || parsed.searchParams.get('store_id') || 101);

    if (lower.endsWith('/manifest.json')) return jsonResponse({});
    if (lower.includes('/banners')) return jsonResponse(banners);
    if (/main-categories|sub-categories|categories/.test(lower)) return jsonResponse(categories);
    if (/settings/.test(lower)) return jsonResponse({ about_app: 'سعي منصة تجمع المتاجر والعملاء في تجربة تسوق محلية سهلة وآمنة.\n\nاكتشف المنتجات والعروض، تواصل مباشرة مع المتاجر، وتابع طلباتك ومحفظتك من مكان واحد.\n\nنسعى في سعي إلى دعم المتاجر المحلية وتسهيل وصول منتجاتها إلى العملاء.', privacy: 'نحترم خصوصيتك ونعالج بياناتك لتشغيل خدمات سعي وتحسين تجربتك.\n\nنستخدم بيانات الحساب والطلبات لتقديم الخدمة، ولا نبيع بياناتك الشخصية. يمكنك إدارة بيانات حسابك وتفضيلات الإشعارات من التطبيق، والتواصل مع الدعم عند طلب المساعدة.', terms_conditions: 'باستخدامك تطبيق سعي فإنك توافق على استخدام الخدمة بمعلومات صحيحة واحترام حقوق المتاجر والمستخدمين.\n\nتتم عمليات الشراء والاستلام وفق تفاصيل المنتج وسياسات المتجر المعروضة قبل تأكيد الطلب.\n\nيمكنك التواصل مع المتجر أو دعم سعي عند وجود مشكلة في طلبك.' });
    if (/favorites/.test(lower)) {
      const favoriteType = parsed.searchParams.get('type') || 'product';
      const items = favoriteType === 'store'
        ? stores.slice(0, 2).map(store => ({ type: 'store', favoriteable_id: store.id, item: store }))
        : products.slice(0, 4).map(product => ({ type: 'product', favoriteable_id: product.id, item: product }));
      return jsonResponse(items);
    }
    if (/reviews/.test(lower)) return jsonResponse([{ id: 801, user: { name: 'سارة محمد' }, rating: 5, comment: 'جودة ممتازة والتعامل كان رائعًا.', created_at: '2026-10-25T11:00:00Z' }, { id: 802, user: { name: 'نور أحمد' }, rating: 4, comment: 'منتج جميل والتوصيل سريع.', created_at: '2026-10-22T11:00:00Z' }, { id: 803, user: { name: 'مريم حسن' }, rating: 5, comment: 'المنتج مطابق للصور ووصل بحالة ممتازة.', created_at: '2026-10-19T11:00:00Z' }], 200, { summary: { average_rating: 4.8, reviews_count: 36 } });
    if (/\/disputes/.test(lower)) return jsonResponse({ status: 'under_review', reason: 'المنتج لا يطابق الوصف', details: 'المنتج المستلم لا يطابق الوصف والصور المعروضة.', messages: [{ body: 'تم استلام بلاغك، وفريق سعي يراجع تفاصيل الطلب.' }] });
    if (/\/chat\/rooms$/.test(lower) && method !== 'GET') return jsonResponse({ id: 901, store: { id: stores[0].id, name: stores[0].name, avatar: stores[0].image } });
    if (/\/store\/products\/\d+$/.test(lower)) {
      const product = products[0];
      return jsonResponse({ product: { id: product.id, name: product.name, sku: 'SA-PR-0101', description: product.description, price: product.price, original_price: product.original_price, discount_percentage: product.discount_percentage, stock_quantity: product.stock_quantity, low_stock_threshold: 5, status: 'published', category: { name: product.category_name }, media: [{ is_primary: true, path: product.image }], features: ['خامة مريحة وعالية الجودة', 'تصميم عملي للاستخدام اليومي'] }, analytics: { sales: { total_revenue: 18900, completed_orders_count: 10, revenue_last_30_days: 7600, daily_sales: [{ revenue: 1200 }, { revenue: 1850 }, { revenue: 1400 }, { revenue: 2200 }] }, ads: { impressions: 1450, clicks: 192, chats_started: 28, orders_attributed: 10, conversion_rate: 5.2 }, reviews: { average_rating: 4.8, total_reviews: 36, rating_distribution: [{ rating: 5, total: 29 }, { rating: 4, total: 5 }, { rating: 3, total: 2 }, { rating: 2, total: 0 }, { rating: 1, total: 0 }], latest: { user_name: 'سارة محمد', rating: 5, comment: 'جودة ممتازة والتعامل كان رائعًا.', created_at: '2026-10-25T11:00:00Z' } } } });
    }
    if (/products\/recommended|featured-products|\/products(?:\/|$)/.test(lower)) {
      if (/\/products\/\d+/.test(lower)) {
        const product = products.find(item => item.id === id) || products[0];
        const images = product.images?.length ? product.images : [product.image];
        return jsonResponse({ ...product, store_id: stores[0].id, media: images.map(url => ({ type: 'image', url })), store: { ...stores[0], avatar: stores[0].image, phone_code: '+20', phone: '1012345678' }, policy: null, latest_review: { user_name: 'سارة محمد', rating: 5, comment: 'جودة ممتازة والتعامل كان رائعًا.' }, similar_products: products.filter(item => item.id !== product.id).slice(0, 3) });
      }
      return jsonResponse(list(products, url));
    }
    if (/stores\/nearby|\/stores(?:\/|$)/.test(lower)) {
      if (/\/stores\/\d+/.test(lower) && !/products|ads/.test(lower)) return jsonResponse({ ...stores.find(item => item.id === id) || stores[0], products, ads: [] });
      return jsonResponse(stores);
    }
    if (/ads/.test(lower)) return jsonResponse(ads);
    if (/competitions/.test(lower)) return jsonResponse(competitions[0] && /\/\d+/.test(lower) && !lower.endsWith('/competitions') ? competitions[0] : competitions);
    if (/notifications/.test(lower)) return jsonResponse(/preferences/.test(lower) ? { email: true, push: true, marketing: false } : notifications);
    if (/orders/.test(lower)) return jsonResponse(/\/\d+/.test(lower) ? orders[0] : orders);
    if (/wallet|transactions|withdrawals|transfers/.test(lower)) {
      if (/\/user\/wallet$/.test(lower)) return jsonResponse(transactions, 200, { wallet: { balance: 2500, pending_balance: 180, currency: 'EGP', has_pending_withdrawal: false } });
      if (/balance|wallet$/.test(lower)) return jsonResponse({ balance: 2500, available_balance: 2500, currency: 'EGP' });
      return jsonResponse(transactions);
    }
    if (/faqs/.test(lower)) return jsonResponse([{ id: 1, question: 'كيف أتابع طلبي؟', answer: 'يمكنك متابعة حالة الطلب من صفحة طلباتي.', category: 'الطلبات' }, { id: 2, question: 'كيف أتواصل مع المتجر؟', answer: 'افتح تفاصيل الطلب ثم اختر التواصل مع المتجر.', category: 'الطلبات' }]);
    if (/governorates/.test(lower)) return jsonResponse([{ id: 1, name: 'القاهرة' }, { id: 2, name: 'الجيزة' }, { id: 3, name: 'الإسكندرية' }]);
    if (/report-reasons/.test(lower)) return jsonResponse([{ id: 1, name: 'معلومات غير صحيحة', title: 'معلومات غير صحيحة' }, { id: 2, name: 'محتوى غير مناسب', title: 'محتوى غير مناسب' }, { id: 3, name: 'مشكلة في المنتج', title: 'مشكلة في المنتج' }]);
    if (/coupons/.test(lower)) return jsonResponse([{ id: 1001, code: 'SAEY20', discount: 20, discount_percentage: 20, status: 'active', starts_at: '2026-10-01', ends_at: '2026-12-31' }]);
    if (/settings|profile|account/.test(lower)) return jsonResponse({ id: 1, name: 'مريم أحمد', phone: '01012345678', email: 'maryam@example.com', balance: 2500, governorate: 'القاهرة', address: 'مدينة نصر، القاهرة' });
    if (/messages/.test(lower)) return jsonResponse([{ id: 1, body: 'أهلًا بك، يسعدنا مساعدتك.', message: 'أهلًا بك، يسعدنا مساعدتك.', is_mine: false, sender_type: 'store', created_at: '2026-10-28T10:20:00Z' }, { id: 2, body: 'هل المنتج متاح؟', message: 'هل المنتج متاح؟', is_mine: true, sender_type: 'user', read_at: '2026-10-28T10:25:00Z', created_at: '2026-10-28T10:24:00Z' }]);
    if (/chats|conversations|\/rooms/.test(lower)) return jsonResponse([{ id: 901, name: stores[0].name, store_name: stores[0].name, last_message: 'أهلًا بك، يسعدنا مساعدتك.', updated_at: '2026-10-28T10:20:00Z', unread_count: 1, avatar: stores[0].image, messages: [{ id: 1, body: 'أهلًا بك، يسعدنا مساعدتك.', message: 'أهلًا بك، يسعدنا مساعدتك.', sender_type: 'store', created_at: '2026-10-28T10:20:00Z' }] }]);
    if (method !== 'GET') return jsonResponse({ success: true, is_favorite: true, is_read: true, message: 'تم تنفيذ الإجراء بنجاح.' });
    return jsonResponse([]);
  };
  window.__SAEY_PREVIEW_DESIGN_MODE__ = true;
})();
