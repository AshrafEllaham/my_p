(() => {
  const origin = location.protocol === 'file:'
    ? 'http://127.0.0.1:8000'
    : ['localhost', '127.0.0.1'].includes(location.hostname)
      ? location.protocol + '//' + location.hostname + ':8000'
      : location.origin;
  const token = localStorage.getItem('saey_api_token');
  const params = new URLSearchParams(location.search);
  const statusLabels = {
    pending_store: 'بانتظار قبول المتجر',
    confirmed: 'تم تأكيد الطلب',
    preparing: 'قيد التجهيز',
    ready_for_completion: 'جاهز للاستلام أو الإتمام',
    awaiting_customer_confirmation: 'بانتظار تأكيدك',
    completed: 'مكتمل',
    cancelled: 'ملغي',
    disputed: 'محل مراجعة',
    refunded: 'تم رد المبلغ'
  };
  const money = value => Number(value || 0).toLocaleString('ar-EG') + ' ج.م';
  const dateText = value => value
    ? new Date(value).toLocaleString('ar-EG', { day: 'numeric', month: 'long', year: 'numeric', hour: 'numeric', minute: '2-digit' })
    : '—';

  function readJson(key) {
    try { return JSON.parse(localStorage.getItem(key) || 'null'); } catch { return null; }
  }

  function unwrap(payload) {
    return payload && Object.prototype.hasOwnProperty.call(payload, 'data') ? payload.data : payload;
  }

  function text(id, value) {
    const element = document.getElementById(id);
    if (element) element.textContent = value == null || value === '' ? '—' : String(value);
  }

  function errorAt(element, message) {
    if (!element) return;
    element.textContent = message;
    element.hidden = false;
  }

  function clearError(element) {
    if (!element) return;
    element.textContent = '';
    element.hidden = true;
  }

  async function api(path, options) {
    const headers = new Headers(options && options.headers ? options.headers : {});
    headers.set('Accept', 'application/json');
    headers.set('Accept-Language', 'ar');
    if (token) headers.set('Authorization', 'Bearer ' + token);
    const response = await fetch(origin + '/api/user/' + path, Object.assign({}, options || {}, { headers }));
    let payload = {};
    try { payload = await response.json(); } catch {}
    if (!response.ok) {
      const validation = payload.errors || payload.data;
      const firstError = validation && !Array.isArray(validation) ? Object.values(validation).flat()[0] : null;
      const error = new Error(firstError || payload.message || 'تعذر تنفيذ الطلب. حاول مرة أخرى.');
      error.field = validation && !Array.isArray(validation) ? Object.keys(validation)[0] : null;
      throw error;
    }
    return payload;
  }

  function orderId() {
    const id = params.get('order_id');
    if (id && /^\d+$/.test(id)) return id;
    const last = readJson('saey_api_order_last');
    const requestedCode = params.get('code');
    if (last && last.id && (!requestedCode || requestedCode === last.number)) return String(last.id);
    return null;
  }

  async function checkout() {
    const button = document.getElementById('confirmOrder');
    if (!button || !token) return;
    const couponInput = document.getElementById('couponInput');
    const feedback = document.getElementById('couponFeedback');
    const feedbackText = document.getElementById('couponMessage');
    const removeCoupon = document.getElementById('removeCoupon');
    const applyCoupon = document.getElementById('applyCoupon');
    text('productMeta', 'جارٍ تحميل بيانات المنتج…');
    text('productTotal', '—');
    text('summarySubtotal', '—');
    text('summaryTotal', '—');
    text('walletBalance', 'جارٍ تحميل الرصيد…');
    const initialProductImage = document.querySelector('.flow-product img');
    if (initialProductImage) initialProductImage.hidden = true;
    const initialProductName = document.querySelector('.flow-product strong');
    if (initialProductName) initialProductName.textContent = 'جارٍ تحميل المنتج…';
    const initialPickup = document.querySelector('.pickup-address strong');
    if (initialPickup) initialPickup.textContent = 'جارٍ تحميل بيانات المتجر…';
    const initialPickupDetails = document.querySelector('.pickup-address small');
    if (initialPickupDetails) initialPickupDetails.textContent = 'سيحدد المتجر تفاصيل الاستلام بعد تأكيد الطلب.';
    const initialChat = document.querySelector('.pickup-actions a:not([target])');
    if (initialChat) initialChat.hidden = true;
    const initialPolicies = document.getElementById('checkoutPolicies');
    if (initialPolicies) initialPolicies.replaceChildren();
    const initialDiscount = document.getElementById('discountRow');
    if (initialDiscount) initialDiscount.hidden = true;
    if (applyCoupon) applyCoupon.onclick = () => {
      if (!couponInput.value.trim()) return;
      feedback.hidden = false;
      feedback.classList.remove('error');
      feedbackText.textContent = 'سيتم التحقق من الكوبون وحساب الخصم عند تأكيد الطلب.';
      removeCoupon.hidden = false;
    };
    if (removeCoupon) removeCoupon.onclick = () => {
      couponInput.value = '';
      feedback.hidden = true;
      removeCoupon.hidden = true;
    };
    button.onclick = async () => {
      const error = document.getElementById('checkoutError');
      const productId = params.get('product_id');
      const quantity = Math.max(1, Number(params.get('qty')) || 1);
      if (!productId || !/^\d+$/.test(productId)) {
        errorAt(error, 'تعذر تحديد المنتج. افتح تأكيد الطلب من صفحة منتج منشور.');
        return;
      }
      clearError(error);
      button.disabled = true;
      button.setAttribute('aria-busy', 'true');
      button.textContent = 'جارٍ تأكيد الطلب…';
      const coupon = couponInput ? couponInput.value.trim() : '';
      const keyName = 'saey_order_attempt:' + productId + ':' + quantity + ':' + coupon;
      let key = sessionStorage.getItem(keyName);
      if (!key) {
        key = crypto.randomUUID();
        sessionStorage.setItem(keyName, key);
      }
      const body = new FormData();
      body.append('product_id', productId);
      body.append('quantity', String(quantity));
      body.append('idempotency_key', key);
      if (coupon) body.append('coupon_code', coupon);
      try {
        const order = unwrap(await api('orders', { method: 'POST', body }));
        localStorage.setItem('saey_api_order_last', JSON.stringify(order));
        sessionStorage.removeItem(keyName);
        text('orderCode', '#' + order.number);
        const storeRow = document.querySelector('#successPage .flow-card .flow-row strong');
        if (storeRow) storeRow.textContent = order.store && order.store.name ? order.store.name : 'المتجر';
        const holdHours = Number(order.policy && order.policy.pickup_hold_hours || 0);
        text('successPickupUntil', holdHours ? holdHours.toLocaleString('ar-EG') + ' ساعة بعد جاهزية الطلب' : 'بعد تأكيد المتجر');
        const query = 'order_id=' + encodeURIComponent(order.id) + '&code=' + encodeURIComponent(order.number);
        document.getElementById('successTrack').href = '21-order-details.html?' + query;
        document.getElementById('successReceipt').href = '26-order-receipt.html?' + query;
        const successChat = document.querySelector('#successPage a[href^="14-chat-conversation"]');
        if (successChat && order.store && order.store.id) {
          successChat.href = '14-chat-conversation.html?store_id=' + encodeURIComponent(order.store.id);
        }
        document.getElementById('checkoutPage').hidden = true;
        document.getElementById('successPage').hidden = false;
      } catch (failure) {
        errorAt(error, failure.message);
        const charge = document.getElementById('chargeWallet');
        if (charge && failure.field === 'wallet') {
          charge.hidden = false;
          charge.style.display = 'grid';
        }
        button.disabled = false;
        button.removeAttribute('aria-busy');
        button.textContent = 'تأكيد والدفع من المحفظة';
      }
    };

    const loadCheckoutData = async () => {
      const error = document.getElementById('checkoutError');
      const productId = params.get('product_id');
      const quantity = Math.max(1, Number(params.get('qty')) || 1);
      if (!productId || !/^\d+$/.test(productId)) {
        button.disabled = true;
        errorAt(error, 'تعذر تحديد المنتج. افتح تأكيد الطلب من صفحة منتج منشور.');
        return;
      }

      button.disabled = true;
      button.setAttribute('aria-busy', 'true');
      button.textContent = 'جارٍ تحميل بيانات الطلب…';
      try {
        const [productPayload, walletPayload] = await Promise.all([
          api('products/' + encodeURIComponent(productId)),
          api('wallet?pagination=off')
        ]);
        const product = unwrap(productPayload);
        const wallet = walletPayload.wallet || {};
        const subtotal = Number(product.price || 0) * quantity;
        const policy = product.policy || {};

        text('productMeta', 'الكمية ' + quantity.toLocaleString('ar-EG'));
        text('productTotal', money(subtotal));
        text('summarySubtotal', money(subtotal));
        text('summaryTotal', money(subtotal));
        text('walletBalance', money(wallet.balance));
        const discountRow = document.getElementById('discountRow');
        if (discountRow) discountRow.hidden = true;

        const productImage = document.querySelector('.flow-product img');
        if (productImage && product.media && product.media[0] && product.media[0].url) {
          productImage.src = product.media[0].url;
          productImage.alt = product.name || 'المنتج';
        }
        const productName = document.querySelector('.flow-product strong');
        if (productName) productName.textContent = product.name || 'المنتج';

        const storeName = product.store && product.store.name ? product.store.name : 'المتجر';
        const pickupName = document.querySelector('.pickup-address strong');
        const pickupDetails = document.querySelector('.pickup-address small');
        if (pickupName) pickupName.textContent = storeName;
        if (pickupDetails) pickupDetails.textContent = 'يحدد المتجر تفاصيل الاستلام بعد تأكيد الطلب.';
        const mapLink = document.querySelector('.pickup-actions a[target="_blank"]');
        if (mapLink) mapLink.hidden = true;
        const chatLink = document.querySelector('.pickup-actions a:not([target])');
        if (chatLink && product.store && product.store.id) {
          chatLink.href = '14-chat-conversation.html?store_id=' + encodeURIComponent(product.store.id);
          chatLink.hidden = false;
        }

        const policySummary = document.getElementById('checkoutPolicies');
        if (policySummary) {
          policySummary.replaceChildren();
          const addPolicy = (title, detail) => {
            const row = document.createElement('div');
            row.className = 'policy-row';
            const copy = document.createElement('div');
            const heading = document.createElement('strong');
            const description = document.createElement('small');
            heading.textContent = title;
            description.textContent = detail || '';
            copy.append(heading, description);
            row.append(copy);
            policySummary.append(row);
          };
          if (policy.pickup_hold_hours) {
            addPolicy('مدة الحجز ' + Number(policy.pickup_hold_hours).toLocaleString('ar-EG') + ' ساعة بعد الجاهزية', policy.pickup_instructions);
          }
          if (policy.returns_enabled) addPolicy('الاسترجاع خلال ' + Number(policy.return_window_days).toLocaleString('ar-EG') + ' يومًا', policy.return_terms);
          if (policy.exchanges_enabled) addPolicy('الاستبدال متاح', policy.exchange_terms);
          if (policy.cancellation_enabled) addPolicy('إلغاء الطلب متاح', policy.cancellation_terms);
        }

        clearError(error);
        button.disabled = false;
        button.removeAttribute('aria-busy');
        button.textContent = 'تأكيد والدفع من المحفظة';
      } catch (failure) {
        errorAt(error, failure.message);
        button.disabled = false;
        button.removeAttribute('aria-busy');
        button.textContent = 'إعادة تحميل بيانات الطلب';
        button.onclick = loadCheckoutData;
      }
    };
    loadCheckoutData();
  }

  function cardFor(order) {
    const status = order.status;
    const group = status === 'completed' || status === 'refunded'
      ? 'past'
      : status === 'cancelled' ? 'cancelled' : 'current';
    const item = order.items && order.items[0] ? order.items[0] : {};
    const card = document.createElement('article');
    card.className = 'order-card' + (group === 'past' ? ' completed-card' : '') + (group === 'cancelled' ? ' cancelled-card' : '');
    card.dataset.code = order.number || '';
    card.dataset.orderId = String(order.id);
    card.dataset.orderGroup = group;
    card.dataset.status = status;
    card.dataset.date = (order.created_at || '').slice(0, 10);
    card.dataset.href = '21-order-details.html?order_id=' + encodeURIComponent(order.id) + '&code=' + encodeURIComponent(order.number || '');
    card.setAttribute('role', 'link');
    card.tabIndex = 0;
    card.setAttribute('aria-label', 'فتح تفاصيل الطلب ' + (order.number || ''));

    const header = document.createElement('header');
    header.className = 'order-card-head';
    const numberBox = document.createElement('div');
    const number = document.createElement('span');
    number.className = 'order-number';
    number.textContent = 'طلب #' + (order.number || '');
    const date = document.createElement('small');
    date.textContent = dateText(order.created_at);
    numberBox.append(number, date);
    const side = document.createElement('div');
    side.className = 'order-card-side';
    const badge = document.createElement('span');
    const badgeClass = status === 'completed' || status === 'refunded'
      ? 'completed'
      : status === 'cancelled' ? 'cancelled' : status === 'ready_for_completion' ? 'ready' : 'preparing';
    badge.className = 'order-status ' + badgeClass;
    badge.textContent = statusLabels[status] || status || '—';
    side.append(badge);
    header.append(numberBox, side);

    const content = document.createElement('div');
    content.className = 'order-card-content';
    const store = document.createElement('div');
    store.className = 'order-store';
    const mark = document.createElement('span');
    mark.className = 'store-mark blue';
    mark.textContent = ((order.store && order.store.name) || 'س').slice(0, 1);
    const storeText = document.createElement('div');
    const storeName = document.createElement('strong');
    storeName.textContent = order.store && order.store.name ? order.store.name : 'المتجر';
    const storeHint = document.createElement('small');
    storeHint.textContent = 'استلام من المتجر';
    storeText.append(storeName, storeHint);
    store.append(mark, storeText);

    const product = document.createElement('div');
    product.className = 'order-product';
    if (item.product_image) {
      const image = document.createElement('img');
      image.src = item.product_image.startsWith('http') ? item.product_image : origin + '/storage/' + item.product_image;
      image.alt = item.product_name || '';
      product.append(image);
    }
    const productText = document.createElement('div');
    const productName = document.createElement('strong');
    productName.textContent = item.product_name || 'المنتج';
    const quantity = document.createElement('small');
    quantity.textContent = 'الكمية ' + Number(item.quantity || 1).toLocaleString('ar-EG');
    productText.append(productName, quantity);
    const total = document.createElement('b');
    total.textContent = money(order.total_amount);
    product.append(productText, total);
    content.append(store, product);
    card.append(header, content);
    if (group === 'past') {
      const note = document.createElement('div');
      note.className = 'completed-note';
      const label = document.createElement('span');
      label.textContent = statusLabels[status] || 'تم إتمام الطلب';
      note.append(label);
      card.append(note);
    }
    return { card, group };
  }

  async function orderList() {
    const panels = [...document.querySelectorAll('.orders-list[role="tabpanel"]')];
    if (!panels.length || !token) return;
    const main = document.querySelector('.orders-page');
    const empty = document.getElementById('ordersEmpty');
    const error = document.getElementById('ordersError');
    const more = document.createElement('button');
    more.type = 'button';
    more.className = 'orders-load-more';
    more.textContent = 'تحميل المزيد';
    more.hidden = true;
    empty.before(more);
    let page = 1;
    let lastPage = 1;
    let loading = false;
    const load = async append => {
      if (loading) return;
      loading = true;
      const requestedPage = append ? page + 1 : 1;
      more.disabled = true;
      more.textContent = 'جارٍ تحميل الطلبات…';
      clearError(error);
      if (!append) {
        panels.forEach(panel => panel.replaceChildren());
      }
      try {
        const payload = await api('orders?pagination=on&page=' + requestedPage + '&limit_per_page=10');
        const pageData = Array.isArray(payload.data) ? payload.data : (payload.data && payload.data.data) || [];
        pageData.forEach(order => {
          const row = cardFor(order);
          const panel = document.getElementById(row.group + '-orders');
          if (panel) {
            const statusFilter = document.getElementById('orderStatusFilter');
            const dateFrom = document.getElementById('orderDateFrom');
            const dateTo = document.getElementById('orderDateTo');
            const selectedStatus = statusFilter ? statusFilter.value : 'all';
            const statusMatches = selectedStatus === 'all' || row.card.dataset.status === selectedStatus || (selectedStatus === 'ready_for_completion' && row.card.dataset.status === 'ready');
            const dateMatches = (!dateFrom || !dateFrom.value || row.card.dataset.date >= dateFrom.value) && (!dateTo || !dateTo.value || row.card.dataset.date <= dateTo.value);
            row.card.classList.toggle('filter-hidden', !statusMatches || !dateMatches);
            panel.append(row.card);
          }
        });
        page = Number(payload.current_page || requestedPage);
        lastPage = Number(payload.last_page || 1);
        more.hidden = page >= lastPage;
        more.textContent = 'تحميل المزيد';
        const active = panels.find(panel => !panel.hidden) || panels[0];
        empty.hidden = active.querySelectorAll('.order-card:not(.filter-hidden)').length > 0;
        document.querySelectorAll('.orders-tab').forEach(tab => {
          const count = document.querySelectorAll('#' + tab.dataset.tab + ' .order-card').length;
          const node = tab.querySelector('span');
          if (node) node.textContent = String(count).replace(/\d/g, digit => '٠١٢٣٤٥٦٧٨٩'[digit]);
        });
        if (main) main.dataset.apiLoaded = 'true';
      } catch (failure) {
        if (!append) empty.hidden = true;
        more.hidden = false;
        more.textContent = 'إعادة المحاولة';
        error.hidden = false;
        const message = error.querySelector('p');
        if (message) message.textContent = failure.message;
      } finally {
        loading = false;
        more.disabled = false;
      }
    };
    more.onclick = () => load(Boolean(main && main.dataset.apiLoaded === 'true'));
    await load(false);
  }

  function displayOrder(order) {
    localStorage.setItem('saey_api_order_last', JSON.stringify(order));
    const status = order.status;
    const item = order.items && order.items[0] ? order.items[0] : {};
    text('detailCode', '#' + order.number);
    text('detailDate', dateText(order.created_at));
    text('detailStatus', statusLabels[status] || status);
    text('detailProductName', item.product_name || 'المنتج');
    text('detailProductMeta', 'الكمية ' + Number(item.quantity || 1).toLocaleString('ar-EG'));
    text('detailTotal', money(order.total_amount));
    text('detailStore', order.store && order.store.name ? order.store.name : 'المتجر');
    const image = document.getElementById('detailProductImage');
    if (image) {
      image.hidden = !item.product_image;
      if (item.product_image) image.src = item.product_image.startsWith('http') ? item.product_image : origin + '/storage/' + item.product_image;
    }
    const policy = order.policy || {};
    text('detailAddress', policy.pickup_instructions || 'تواصل مع المتجر لتأكيد تفاصيل الاستلام.');
    const mapLink = document.querySelector('.pickup-actions a[target="_blank"]');
    if (mapLink) mapLink.hidden = true;
    const chatLink = document.getElementById('detailChat');
    if (chatLink && order.store && order.store.id) {
      chatLink.href = '14-chat-conversation.html?store_id=' + encodeURIComponent(order.store.id);
      chatLink.hidden = false;
    }
    // لا يعرض preview رمز استلام محليًا؛ رمز الخادم يحتاج تدفق تحقق مخصصًا.
    const pickupIdentity = document.getElementById('pickupIdentity');
    if (pickupIdentity) pickupIdentity.hidden = true;
    const history = Array.isArray(order.status_history) ? order.status_history : [];
    const historyTime = status => {
      const entry = history.find(row => row.to_status === status);
      return entry ? entry.created_at : null;
    };
    text('confirmedMark', dateText(order.confirmed_at || historyTime('confirmed')));
    text('preparingMark', dateText(order.preparing_at || historyTime('preparing')));
    text('readyMark', dateText(order.ready_at || historyTime('ready_for_completion')));
    text('receivedMark', dateText(order.completed_at || historyTime('completed')));
    const query = 'order_id=' + encodeURIComponent(order.id) + '&code=' + encodeURIComponent(order.number);
    const receipt = document.getElementById('receiptLink');
    if (receipt) receipt.href = '26-order-receipt.html?' + query;
    const completed = status === 'completed';
    const completedAt = order.completed_at ? Date.parse(order.completed_at) : 0;
    const returnWindowDays = Number(policy.return_window_days || 0);
    const withinReturnWindow = completedAt > 0 && returnWindowDays > 0 && Date.now() <= completedAt + returnWindowDays * 86400000;
    const hasReturn = Boolean(order.return_request);
    const cancel = document.getElementById('cancelOrder');
    const rate = document.getElementById('rateOrder');
    const refund = document.getElementById('refundOrder');
    const exchange = document.getElementById('exchangeOrder');
    if (cancel) cancel.hidden = !(policy.cancellation_enabled && ['pending_store', 'confirmed'].includes(status));
    if (rate) rate.hidden = !(completed && !order.review);
    if (refund) refund.hidden = !(completed && policy.returns_enabled && withinReturnWindow && !hasReturn);
    if (exchange) exchange.hidden = !(completed && policy.exchanges_enabled && withinReturnWindow && !hasReturn);
    const returnStatus = document.getElementById('returnStatusLink');
    if (returnStatus && hasReturn) {
      returnStatus.hidden = false;
      returnStatus.style.display = 'grid';
      returnStatus.href = '26-return-status.html?order_id=' + encodeURIComponent(order.id) + '&code=' + encodeURIComponent(order.number);
    }
    const cancelReason = document.getElementById('cancelReason');
    if (cancelReason) {
      cancelReason.hidden = status !== 'cancelled';
      const reason = cancelReason.querySelector('p');
      if (reason) reason.textContent = order.cancellation_reason || 'تم إلغاء الطلب.';
    }
    const tracking = document.getElementById('trackingCard');
    if (tracking) tracking.hidden = status === 'cancelled';

    const actionMessage = document.getElementById('orderActionMessage');
    const cancelRequest = document.getElementById('confirmCancel');
    if (rate) rate.onclick = () => { location.href = '22-rate-order.html?' + query; };
    if (cancel) cancel.onclick = () => document.getElementById('cancelDialog').showModal();
    if (cancelRequest) cancelRequest.onclick = async event => {
      event.preventDefault();
      cancelRequest.disabled = true;
      try {
        const updated = unwrap(await api('orders/' + order.id + '/cancel', { method: 'PATCH', body: new FormData() }));
        document.getElementById('cancelDialog').close();
        displayOrder(updated);
        actionMessage.className = 'flow-message success';
        actionMessage.textContent = 'تم إلغاء الطلب وإعادة المبلغ إلى محفظتك.';
        actionMessage.hidden = false;
      } catch (failure) {
        document.getElementById('cancelDialog').close();
        actionMessage.className = 'flow-message';
        actionMessage.textContent = failure.message;
        actionMessage.hidden = false;
      } finally {
        cancelRequest.disabled = false;
      }
    };
    if (refund) refund.onclick = () => { location.href = '26-return-request.html?' + query; };
    if (exchange) exchange.onclick = () => { location.href = '26-return-request.html?' + query + '&mode=exchange'; };
  }

  async function orderDetails() {
    if (!document.getElementById('detailCode')) return;
    if (!token) return;
    const pickupIdentity = document.getElementById('pickupIdentity');
    if (pickupIdentity) pickupIdentity.hidden = true;
    const mapLink = document.querySelector('.pickup-actions a[target="_blank"]');
    if (mapLink) mapLink.hidden = true;
    const detailChat = document.getElementById('detailChat');
    if (detailChat) detailChat.hidden = true;
    const tracking = document.getElementById('trackingCard');
    if (tracking) tracking.hidden = true;
    const cancelReason = document.getElementById('cancelReason');
    if (cancelReason) cancelReason.hidden = true;
    ['detailCode', 'detailDate', 'detailStatus', 'detailProductName', 'detailProductMeta', 'detailTotal', 'detailStore']
      .forEach(id => text(id, 'جارٍ تحميل بيانات الطلب…'));
    text('detailAddress', 'جارٍ تحميل تفاصيل الاستلام…');
    ['confirmedMark', 'preparingMark', 'readyMark', 'receivedMark'].forEach(id => text(id, '—'));
    ['cancelOrder', 'rateOrder', 'refundOrder', 'exchangeOrder', 'returnStatusLink'].forEach(id => {
      const action = document.getElementById(id);
      if (action) action.hidden = true;
    });
    const id = orderId();
    const message = document.getElementById('orderActionMessage');
    if (!id) {
      message.className = 'flow-message';
      errorAt(message, 'افتح تفاصيل الطلب من قائمة طلبات الحساب المسجل.');
      return;
    }
    try {
      displayOrder(unwrap(await api('orders/' + encodeURIComponent(id))));
    } catch (failure) {
      message.className = 'flow-message';
      errorAt(message, failure.message);
    }
  }

  async function review() {
    const submit = document.getElementById('submitRating');
    const form = document.getElementById('ratingForm');
    if (!submit || !form || !token) return;
    submit.disabled = true;
    text('ratingProduct', 'جارٍ تحميل المنتج…');
    text('ratingStore', 'جارٍ تحميل المتجر…');
    const id = orderId();
    const error = document.getElementById('ratingError');
    if (!id) {
      errorAt(error, 'افتح التقييم من تفاصيل الطلب.');
      return;
    }
    let order;
    try {
      order = unwrap(await api('orders/' + encodeURIComponent(id)));
    } catch (failure) {
      errorAt(error, failure.message);
      submit.disabled = false;
      submit.textContent = 'إعادة تحميل التقييم';
      submit.onclick = () => {
        submit.onclick = null;
        submit.textContent = 'إرسال التقييم';
        review();
      };
      return;
    }
    clearError(error);
    const item = order.items && order.items[0] ? order.items[0] : {};
    text('ratingProduct', item.product_name || 'المنتج');
    text('ratingStore', order.store && order.store.name ? order.store.name : 'المتجر');
    document.getElementById('ratingBack').href = '21-order-details.html?order_id=' + order.id + '&code=' + encodeURIComponent(order.number);
    if (order.review) {
      form.hidden = true;
      document.getElementById('ratingDone').hidden = false;
      return;
    }
    let rating = 0;
    const stars = [...document.querySelectorAll('.rating-stars button')];
    stars.forEach((star, index) => {
      star.onclick = () => {
        rating = index + 1;
        clearError(error);
        stars.forEach((node, position) => node.classList.toggle('active', position < rating));
      };
    });
    submit.onclick = async () => {
      if (!rating) {
        errorAt(error, 'اختر عدد النجوم أولًا.');
        return;
      }
      submit.disabled = true;
      const body = new FormData();
      body.append('rating', String(rating));
      body.append('comment', document.getElementById('ratingComment').value.trim());
      try {
        await api('orders/' + order.id + '/review', { method: 'POST', body });
        form.hidden = true;
        document.getElementById('ratingDone').hidden = false;
      } catch (failure) {
        errorAt(error, failure.message);
      } finally {
        submit.disabled = false;
      }
    };
    submit.disabled = false;
  }

  async function receipt() {
    if (!document.getElementById('receiptCode') || !token) return;
    text('receiptCode', 'جارٍ تحميل الإيصال…');
    text('receiptDate', '—');
    text('receiptStore', '—');
    text('receiptProduct', 'جارٍ تحميل التفاصيل…');
    text('receiptSubtotal', '—');
    text('receiptDiscount', '—');
    text('receiptTotal', '—');
    const discountRow = document.getElementById('receiptDiscountRow');
    if (discountRow) discountRow.hidden = true;
    const id = orderId();
    if (!id) {
      const note = document.querySelector('.flow-note span');
      if (note) note.textContent = 'افتح الإيصال من تفاصيل الطلب المسجل.';
      return;
    }
    try {
      const result = unwrap(await api('orders/' + encodeURIComponent(id) + '/receipt'));
      const snapshot = result.snapshot || {};
      const order = readJson('saey_api_order_last') || {};
      const code = snapshot.order_number || order.number || params.get('code');
      text('receiptCode', '#' + (code || result.receipt_number));
      text('receiptDate', dateText(snapshot.paid_at || result.issued_at));
      text('receiptStore', snapshot.store_name || (order.store && order.store.name) || 'المتجر');
      text('receiptProduct', (snapshot.product_name || 'المنتج') + ' × ' + Number(snapshot.quantity || 1).toLocaleString('ar-EG'));
      text('receiptSubtotal', money(snapshot.subtotal));
      text('receiptDiscount', '− ' + money(snapshot.discount_amount));
      text('receiptTotal', money(snapshot.total_amount));
      if (discountRow) discountRow.hidden = Number(snapshot.discount_amount || 0) <= 0;
      document.getElementById('receiptBack').href = '21-order-details.html?order_id=' + id + '&code=' + encodeURIComponent(code || '');
    } catch (failure) {
      const note = document.querySelector('.flow-note span');
      if (note) note.textContent = failure.message;
    }
  }

  async function returnRequest() {
    const submit = document.getElementById('submitReturn');
    if (!submit || !token) return;
    submit.disabled = true;
    submit.textContent = 'جارٍ تحميل الطلب…';
    text('productName', 'جارٍ تحميل المنتج…');
    text('orderCode', 'جارٍ تحميل بيانات الطلب…');
    text('refundAmount', '—');
    text('summaryAmount', '—');
    const initialImage = document.getElementById('requestProductImage');
    if (initialImage) initialImage.hidden = true;
    const id = orderId();
    const error = document.getElementById('returnError');
    if (!id) {
      errorAt(error, 'افتح طلب ما بعد البيع من تفاصيل الطلب.');
      return;
    }
    let order;
    try {
      order = unwrap(await api('orders/' + encodeURIComponent(id)));
    } catch (failure) {
      errorAt(error, failure.message);
      submit.disabled = false;
      submit.textContent = 'إعادة تحميل بيانات الطلب';
      submit.onclick = () => {
        submit.onclick = null;
        returnRequest();
      };
      return;
    }
    clearError(error);
    const item = order.items && order.items[0] ? order.items[0] : {};
    const mode = params.get('mode') === 'exchange' ? 'exchange' : 'refund';
    const query = 'order_id=' + order.id + '&code=' + encodeURIComponent(order.number);
    document.getElementById('backLink').href = '21-order-details.html?' + query;
    text('productName', item.product_name || 'المنتج');
    text('orderCode', '#' + order.number + ' · الكمية ' + Number(item.quantity || 1).toLocaleString('ar-EG'));
    text('refundAmount', money(order.total_amount));
    text('summaryAmount', money(order.total_amount));
    const image = document.getElementById('requestProductImage');
    if (image) {
      image.hidden = !item.product_image;
      if (item.product_image) image.src = item.product_image.startsWith('http') ? item.product_image : origin + '/storage/' + item.product_image;
    }
    if (mode === 'exchange') {
      document.title = 'سعي — طلب استبدال';
      document.getElementById('requestTitle').textContent = 'طلب استبدال';
      document.getElementById('productSectionTitle').textContent = 'المنتج المطلوب استبداله';
      document.getElementById('reasonLabel').textContent = 'سبب الاستبدال';
      document.getElementById('resolutionTitle').textContent = 'طريقة الحل';
      document.getElementById('resolutionLabel').textContent = 'الإجراء المطلوب';
      document.getElementById('resolutionValue').textContent = 'منتج بديل';
      submit.textContent = 'إرسال طلب الاستبدال';
    }
    submit.disabled = false;
    submit.textContent = mode === 'exchange' ? 'إرسال طلب الاستبدال' : 'إرسال طلب الاسترجاع';
    submit.onclick = async () => {
      const reason = document.getElementById('returnReason').value.trim();
      const details = document.getElementById('returnDetails').value.trim();
      if (!reason || details.length < 10) {
        errorAt(error, 'اختر سبب الطلب واكتب تفاصيل لا تقل عن ١٠ أحرف.');
        return;
      }
      submit.disabled = true;
      const body = new FormData();
      body.append('reason', reason);
      body.append('details', details);
      [...document.getElementById('returnPhotos').files].slice(0, 3).forEach(file => body.append('photos[]', file));
      const action = mode === 'exchange' ? 'exchange-requests' : 'return-requests';
      try {
        const result = unwrap(await api('orders/' + order.id + '/' + action, { method: 'POST', body }));
        localStorage.setItem('saey_api_return_last', JSON.stringify(Object.assign({}, result, {
          order_id: order.id,
          order_number: order.number,
          product_name: item.product_name,
          store_id: order.store && order.store.id,
          mode
        })));
        location.href = '26-return-status.html?' + query + '&request_id=' + encodeURIComponent(result.public_id) + '&mode=' + mode + '&submitted=1';
      } catch (failure) {
        errorAt(error, failure.message);
        submit.disabled = false;
      }
    };
  }

  async function returnStatus() {
    if (!document.getElementById('returnId') || !token) return;
    const message = document.getElementById('submittedMessage');
    const storeChat = document.getElementById('returnStoreChat');
    if (storeChat) storeChat.hidden = true;
    message.hidden = true;
    text('returnId', 'جارٍ تحميل الطلب…');
    text('originalOrder', '—');
    text('returnValue', '—');
    text('returnProduct', 'جارٍ تحميل التفاصيل…');
    text('returnReasonText', '—');
    text('returnPhotosCount', '—');
    const initialSteps = [1, 2, 3, 4].map(number => document.getElementById('returnStep' + number));
    initialSteps.forEach((step, index) => {
      step.classList.remove('done', 'active');
      step.querySelector('i').textContent = '١٢٣٤'[index];
    });
    const orderIdValue = params.get('order_id');
    const cached = readJson('saey_api_return_last');
    const requestedReturnId = params.get('request_id');
    let saved = cached && (
      (orderIdValue && String(cached.order_id) === String(orderIdValue))
      || (requestedReturnId && cached.public_id === requestedReturnId)
    ) ? cached : null;
    let storeId = saved && saved.store_id ? saved.store_id : null;
    if (orderIdValue && /^\d+$/.test(orderIdValue)) {
      try {
        const order = unwrap(await api('orders/' + encodeURIComponent(orderIdValue)));
        const item = order.items && order.items[0] ? order.items[0] : {};
        storeId = order.store && order.store.id ? order.store.id : storeId;
        saved = order.return_request ? Object.assign({}, order.return_request, {
          order_id: order.id,
          order_number: order.number,
          product_name: item.product_name,
          store_id: storeId,
          mode: order.return_request.resolution_type
        }) : saved;
        if (saved) localStorage.setItem('saey_api_return_last', JSON.stringify(saved));
      } catch (failure) {
        if (!saved) {
          message.hidden = false;
          message.className = 'flow-message';
          message.textContent = failure.message;
          return;
        }
      }
    }
    if (!saved) {
      message.hidden = false;
      message.className = 'flow-message';
      message.textContent = 'لا يوجد طلب استرجاع أو استبدال مرتبط بهذا الرابط.';
      return;
    }
    if (storeChat && storeId) {
      storeChat.href = '14-chat-conversation.html?store_id=' + encodeURIComponent(storeId);
      storeChat.hidden = false;
    }
    const exchange = saved.mode === 'exchange' || saved.resolution_type === 'exchange';
    if (exchange) {
      document.title = 'سعي — متابعة الاستبدال';
      document.querySelector('.flow-head h1').textContent = 'متابعة الاستبدال';
      document.querySelector('#returnStep1 strong').textContent = 'تم إرسال طلب الاستبدال';
      document.querySelector('#returnStep4 strong').textContent = 'إتمام الاستبدال';
      document.querySelector('#returnStep4 small').textContent = 'بعد تسليم المنتج واستلام البديل';
    }
    const query = 'order_id=' + encodeURIComponent(saved.order_id) + '&code=' + encodeURIComponent(saved.order_number || '');
    document.querySelector('.flow-head .flow-back').href = '21-order-details.html?' + query;
    text('returnId', saved.public_id || saved.id);
    text('originalOrder', '#' + saved.order_number);
    text('returnValue', exchange ? 'منتج بديل' : money(saved.requested_amount));
    text('returnProduct', saved.product_name || 'المنتج');
    text('returnReasonText', saved.reason);
    const photoCount = Array.isArray(saved.media) ? saved.media.length : 0;
    text('returnPhotosCount', photoCount ? photoCount.toLocaleString('ar-EG') + ' صور' : 'لا توجد صور');
    document.getElementById('submittedMessage').hidden = params.get('submitted') !== '1';
    const status = saved.status;
    const steps = [1, 2, 3, 4].map(number => document.getElementById('returnStep' + number));
    const finishedSteps = status === 'pending_review' ? 1
      : ['approved', 'rejected', 'cancelled'].includes(status) ? 2
        : status === 'product_received' ? 3
          : ['refunded', 'exchanged'].includes(status) ? 4 : 1;
    steps.forEach((step, index) => {
      step.classList.toggle('done', index < finishedSteps);
      step.classList.toggle('active', index === finishedSteps && finishedSteps < steps.length);
      step.querySelector('i').textContent = index < finishedSteps ? '✓' : '١٢٣٤'[index];
    });
    const statusLabel = {
      pending_review: 'تم إرسال الطلب وسيظهر قرار المتجر هنا.',
      approved: 'وافق المتجر على الطلب.',
      rejected: saved.merchant_note || 'رفض المتجر الطلب.',
      product_received: 'استلم المتجر المنتج ويستكمل الإجراء.',
      refunded: 'اكتمل الاسترجاع وأعيد المبلغ إلى محفظة سعي.',
      exchanged: 'اكتمل الاستبدال.',
      cancelled: 'تم إلغاء طلب ما بعد البيع.'
    };
    const statusMessage = document.getElementById('submittedMessage');
    statusMessage.hidden = false;
    statusMessage.className = ['rejected', 'cancelled'].includes(status) ? 'flow-message' : 'flow-message success';
    statusMessage.textContent = statusLabel[status] || 'حالة الطلب: ' + (status || 'قيد المراجعة');
  }

  checkout();
  orderList();
  orderDetails();
  review();
  receipt();
  returnRequest();
  returnStatus();
})();
