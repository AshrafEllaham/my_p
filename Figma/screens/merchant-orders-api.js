(() => {
  const token = localStorage.getItem('saey_api_token');
  if (!token) return;

  const origin = location.protocol === 'file:'
    ? 'http://127.0.0.1:8000'
    : ['localhost', '127.0.0.1'].includes(location.hostname)
      ? `${location.protocol}//${location.hostname}:8000`
      : location.origin;
  const stateLabels = {
    pending_store: ['طلب جديد', 'تأكيد الطلب', 'pending'],
    confirmed: ['تم التأكيد', 'بدء التجهيز', 'pending'],
    preparing: ['قيد التجهيز', 'جاهز للاستلام', 'preparing'],
    ready_for_completion: ['جاهز للاستلام', 'بانتظار المستخدم', 'live'],
    awaiting_customer_confirmation: ['بانتظار تأكيد المستخدم', 'بانتظار المستخدم', 'preparing'],
    completed: ['مكتمل', 'عرض التفاصيل', 'completed'],
    cancelled: ['ملغي', 'عرض التفاصيل', 'pending'],
    disputed: ['محل مراجعة', 'عرض التفاصيل', 'pending'],
    refunded: ['مسترد', 'عرض التفاصيل', 'pending']
  };
  const api = async (path, options = {}) => {
    const response = await fetch(`${origin}/api/store/${path}`, {
      ...options,
      headers: {
        Accept: 'application/json',
        'Accept-Language': 'ar',
        Authorization: `Bearer ${token}`,
        ...(options.headers || {})
      }
    });
    const payload = await response.json();
    if (!response.ok) {
      throw new Error(Object.values(payload.errors || {}).flat()[0] || payload.message || 'تعذر تنفيذ طلب المتجر.');
    }
    return payload;
  };
  const money = value => `${Number(value || 0).toLocaleString('ar-EG')} ج.م`;
  const dateLabel = value => value
    ? new Intl.DateTimeFormat('ar-EG', { day: 'numeric', month: 'short', hour: 'numeric', minute: '2-digit' }).format(new Date(value))
    : '—';
  const nextStatus = status => ({
    pending_store: 'confirmed',
    confirmed: 'preparing',
    preparing: 'ready_for_completion'
  })[status] || null;
  const actionLabels = {
    pending_store: 'تأكيد الطلب',
    confirmed: 'بدء التجهيز',
    preparing: 'جاهز للاستلام',
    ready_for_completion: 'التحقق من رمز الاستلام'
  };

  const list = document.querySelector('.merchant-orders-list');
  if (list) {
    const search = document.getElementById('orderSearch');
    const empty = document.getElementById('ordersEmptyState');
    const tabs = [...document.querySelectorAll('.orders-manager .product-tabs button')];
    const summary = document.querySelector('.orders-summary');
    if (summary) summary.hidden = true;
    document.querySelectorAll('.orders-manager .product-tabs b').forEach(count => { count.hidden = true; });
    const pagination = document.createElement('nav');
    pagination.className = 'merchant-order-pagination';
    pagination.setAttribute('aria-label', 'صفحات الطلبات');
    pagination.innerHTML = '<button type="button" data-page="previous">السابق</button><span></span><button type="button" data-page="next">التالي</button>';
    list.before(pagination);
    const pageLabel = pagination.querySelector('span');
    const previousPage = pagination.querySelector('[data-page="previous"]');
    const nextPage = pagination.querySelector('[data-page="next"]');
    let orders = [];
    let activeStatus = 'all';
    let activePeriod = 'all';
    let currentPage = 1;
    let lastPage = 1;
    let loadError;
    let searchTimeout;

    const statusMatches = (order, filter) => {
      if (filter === 'all') return true;
      if (filter === 'new') return order.status === 'pending_store';
      if (filter === 'preparing') return ['confirmed', 'preparing'].includes(order.status);
      if (filter === 'ready') return ['ready_for_completion', 'awaiting_customer_confirmation'].includes(order.status);
      return order.status === filter;
    };
    const periodMatches = (order, period) => {
      if (period === 'all' || !order.created_at) return true;
      const created = new Date(order.created_at);
      const elapsed = Date.now() - created.getTime();
      return period === 'today'
        ? created.toDateString() === new Date().toDateString()
        : elapsed <= 7 * 24 * 60 * 60 * 1000;
    };
    const applyFilters = () => {
      const query = search.value.trim().toLocaleLowerCase();
      let visible = 0;
      list.querySelectorAll('.merchant-order[data-api-order]').forEach(card => {
        const order = orders.find(item => String(item.id) === card.dataset.apiOrder);
        const matches = order && statusMatches(order, activeStatus) && periodMatches(order, activePeriod)
          && `${order.number} ${order.customer?.name || ''} ${(order.items || []).map(item => item.product_name).join(' ')}`.toLocaleLowerCase().includes(query);
        card.hidden = !matches;
        if (matches) visible++;
      });
      empty.hidden = visible !== 0;
      document.getElementById('visibleOrdersCount').textContent = visible.toLocaleString('ar-EG');
      const total = Number(list.dataset.total || orders.length);
      document.querySelector('.orders-list-heading > span').textContent = `${visible.toLocaleString('ar-EG')} من ${total.toLocaleString('ar-EG')} طلب`;
    };
    const makeCard = order => {
      const card = document.createElement('article');
      const item = order.items?.[0] || {};
      const customer = order.customer || {};
      const [label, action, badge] = stateLabels[order.status] || ['حالة الطلب', 'عرض التفاصيل', 'pending'];
      const buttonLabel = actionLabels[order.status] || action;
      card.className = 'merchant-order';
      card.dataset.apiOrder = String(order.id);
      card.dataset.state = order.status;
      card.dataset.search = `${order.number} ${customer.name || ''} ${item.product_name || ''}`;
      card.setAttribute('role', 'link');
      card.tabIndex = 0;

      const header = document.createElement('div');
      header.className = 'merchant-order-head';
      const identity = document.createElement('div');
      const number = document.createElement('strong');
      number.dir = 'ltr';
      number.textContent = `#${order.number}`;
      const time = document.createElement('time');
      time.dateTime = order.created_at || '';
      time.textContent = dateLabel(order.created_at);
      identity.append(number, time);
      const status = document.createElement('span');
      status.className = `status ${badge}`;
      status.textContent = label;
      header.append(identity, status);

      const customerRow = document.createElement('div');
      customerRow.className = 'order-customer';
      const avatar = document.createElement('span');
      avatar.className = 'customer-avatar';
      avatar.textContent = (customer.name || 'م').slice(0, 1);
      const customerInfo = document.createElement('div');
      const customerName = document.createElement('strong');
      customerName.textContent = customer.name || 'عميل';
      const pickup = document.createElement('small');
      pickup.textContent = 'استلام من المتجر';
      customerInfo.append(customerName, pickup);
      const total = document.createElement('b');
      total.className = 'order-total';
      total.textContent = money(order.total_amount);
      customerRow.append(avatar, customerInfo, total);

      const productRow = document.createElement('div');
      productRow.className = 'order-product';
      const productIcon = document.createElement('span');
      productIcon.innerHTML = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m4 8 8-4 8 4-8 4-8-4Z"/><path d="M4 8v8l8 4 8-4V8M12 12v8"/></svg>';
      const productInfo = document.createElement('div');
      const productName = document.createElement('strong');
      productName.textContent = item.product_name || 'طلب متجر';
      const quantity = document.createElement('small');
      quantity.textContent = `الكمية: ${Number(item.quantity || 1).toLocaleString('ar-EG')}`;
      productInfo.append(productName, quantity);
      productRow.append(productIcon, productInfo);

      const actions = document.createElement('div');
      actions.className = 'order-actions';
      const button = document.createElement('button');
      button.className = `order-primary${nextStatus(order.status) ? '' : ' completed-action'}`;
      button.type = 'button';
      button.textContent = buttonLabel;
      const chevron = document.createElement('span');
      chevron.className = 'order-card-chevron';
      chevron.setAttribute('aria-hidden', 'true');
      chevron.innerHTML = '<svg viewBox="0 0 24 24"><path d="m15 18-6-6 6-6"/></svg>';
      actions.append(button, chevron);
      card.append(header, customerRow, productRow, actions);

      const openDetails = () => { location.href = `21-distributor-order-details.html?order_id=${encodeURIComponent(order.id)}`; };
      card.addEventListener('click', event => { if (!event.target.closest('button,a')) openDetails(); });
      card.addEventListener('keydown', event => { if (event.key === 'Enter') openDetails(); });
      button.addEventListener('click', async event => {
        event.stopPropagation();
        const statusToSet = nextStatus(order.status);
        if (!statusToSet) return openDetails();
        button.disabled = true;
        try {
          const form = new FormData();
          form.append('status', statusToSet);
          await api(`orders/${encodeURIComponent(order.id)}/status`, { method: 'PATCH', body: form });
          await loadOrders();
        } catch (error) {
          showError(error.message);
          button.disabled = false;
        }
      });
      return card;
    };
    const showError = message => {
      if (!loadError) {
        loadError = document.createElement('p');
        loadError.className = 'merchant-orders-api-error';
        loadError.setAttribute('role', 'alert');
        list.before(loadError);
      }
      loadError.textContent = message;
    };
    async function loadOrders(page = 1) {
      try {
        const query = new URLSearchParams({ pagination: 'on', limit_per_page: '20', page: String(page) });
        const statusQuery = ({ new: 'pending_store', preparing: 'preparing', ready: 'ready_for_completion', completed: 'completed' })[activeStatus];
        if (statusQuery) query.set('status', statusQuery);
        if (search.value.trim()) query.set('search', search.value.trim());
        const firstPage = await api(`orders?${query.toString()}`);
        orders = firstPage.data || [];
        currentPage = Number(firstPage.current_page || page);
        lastPage = Number(firstPage.last_page || firstPage.meta?.last_page || 1);
        list.dataset.total = String(firstPage.total || orders.length);
        list.replaceChildren(empty);
        orders.forEach(order => list.insertBefore(makeCard(order), empty));
        if (loadError) loadError.remove();
        loadError = null;
        pagination.hidden = lastPage <= 1;
        pageLabel.textContent = `صفحة ${currentPage.toLocaleString('ar-EG')} من ${lastPage.toLocaleString('ar-EG')}`;
        previousPage.disabled = currentPage <= 1;
        nextPage.disabled = currentPage >= lastPage;
        applyFilters();
      } catch (error) {
        showError(error.message);
      }
    }

    search.addEventListener('input', () => {
      window.clearTimeout(searchTimeout);
      searchTimeout = window.setTimeout(() => loadOrders(1), 300);
    });
    tabs.forEach(button => button.addEventListener('click', () => {
      activeStatus = button.dataset.state;
      loadOrders(1);
    }));
    document.querySelectorAll('#orderStateFilters button').forEach(button => button.addEventListener('click', () => {
      activeStatus = button.dataset.value;
    }));
    document.querySelectorAll('#orderPeriodFilters button').forEach(button => button.addEventListener('click', () => {
      activePeriod = button.dataset.value;
    }));
    document.getElementById('applyOrderFilters').addEventListener('click', () => {
      loadOrders(1);
    });
    document.getElementById('resetOrderFilters').addEventListener('click', () => {
      activeStatus = 'all';
      activePeriod = 'all';
      loadOrders(1);
    });
    previousPage.addEventListener('click', () => loadOrders(currentPage - 1));
    nextPage.addEventListener('click', () => loadOrders(currentPage + 1));
    loadOrders();
    return;
  }

  const orderId = new URLSearchParams(location.search).get('order_id');
  if (!orderId || !/^\d+$/.test(orderId)) return;

  const advanceButton = document.getElementById('advanceOrderAction');
  const freshAdvanceButton = advanceButton.cloneNode(true);
  advanceButton.replaceWith(freshAdvanceButton);
  const handoverDialog = document.getElementById('handoverDialog');
  document.getElementById('rejectOrderAction').hidden = true;
  document.getElementById('noShowAction').hidden = true;
  let order;
  let errorBox;

  const showDetailError = message => {
    if (!errorBox) {
      errorBox = document.createElement('p');
      errorBox.className = 'handover-error';
      errorBox.setAttribute('role', 'alert');
      freshAdvanceButton.before(errorBox);
    }
    errorBox.textContent = message;
  };
  const renderOrder = () => {
    const customer = order.customer || {};
    const item = order.items?.[0] || {};
    const config = stateLabels[order.status] || ['حالة الطلب', 'عرض التفاصيل', 'pending'];
    const stage = ({ pending_store: 0, confirmed: 1, preparing: 2, ready_for_completion: 3, awaiting_customer_confirmation: 3, completed: 4 })[order.status] || 0;
    document.getElementById('orderCode').textContent = `#${order.number}`;
    document.getElementById('orderDate').textContent = dateLabel(order.created_at);
    document.getElementById('heroCustomer').textContent = customer.name || 'عميل';
    document.getElementById('heroTotal').textContent = money(order.total_amount);
    document.getElementById('orderProductName').textContent = item.product_name || '—';
    document.getElementById('orderProductMeta').textContent = `الكمية: ${Number(item.quantity || 1).toLocaleString('ar-EG')}`;
    document.getElementById('orderProductPrice').textContent = money(item.line_total || order.total_amount);
    document.getElementById('customerAvatar').textContent = (customer.name || 'م').slice(0, 1);
    document.getElementById('customerName').textContent = customer.name || 'عميل';
    document.getElementById('customerPhone').textContent = `${customer.phone_code || ''} ${customer.phone || ''}`.trim() || '—';
    document.getElementById('pickupTime').textContent = order.pickup_expires_at ? `قبل ${dateLabel(order.pickup_expires_at)}` : 'حسب سياسة المتجر';
    document.getElementById('customerNote').textContent = '—';
    document.getElementById('subtotal').textContent = money(order.subtotal);
    document.getElementById('discountValue').textContent = money(order.discount_amount);
    document.getElementById('paymentTotal').textContent = money(order.total_amount);
    const badge = document.getElementById('orderStatusBadge');
    badge.textContent = config[0];
    badge.className = config[2];
    document.getElementById('progressCaption').textContent = order.status === 'ready_for_completion'
      ? 'بانتظار تأكيد المستخدم'
      : order.status === 'completed' ? 'اكتمل الطلب' : config[0];
    document.querySelectorAll('#orderProgress [data-step]').forEach(step => step.classList.toggle('done', Number(step.dataset.step) <= stage));
    freshAdvanceButton.textContent = actionLabels[order.status] || (order.status === 'completed' ? 'تم إتمام الطلب' : 'بانتظار المستخدم');
    freshAdvanceButton.disabled = !nextStatus(order.status) && order.status !== 'ready_for_completion';
  };

  freshAdvanceButton.addEventListener('click', async () => {
    if (order.status === 'ready_for_completion') {
      document.getElementById('requestCustomerConfirmation').hidden = true;
      document.getElementById('showPickupCodeForm').hidden = true;
      document.getElementById('pickupCodeForm').hidden = false;
      document.getElementById('pickupCodeInput').value = '';
      document.getElementById('pickupCodeError').hidden = true;
      handoverDialog.showModal();
      document.getElementById('pickupCodeInput').focus();
      return;
    }
    const status = nextStatus(order.status);
    if (!status) return;
    freshAdvanceButton.disabled = true;
    try {
      const form = new FormData();
      form.append('status', status);
      const response = await api(`orders/${encodeURIComponent(orderId)}/status`, { method: 'PATCH', body: form });
      order = response.data;
      renderOrder();
    } catch (error) {
      showDetailError(error.message);
      freshAdvanceButton.disabled = false;
    }
  });

  document.getElementById('verifyPickupCode').onclick = async () => {
    const input = document.getElementById('pickupCodeInput');
    const error = document.getElementById('pickupCodeError');
    error.hidden = true;
    const form = new FormData();
    form.append('pickup_qr_token', input.value.trim());
    try {
      const response = await api(`orders/${encodeURIComponent(orderId)}/confirm-pickup`, { method: 'POST', body: form });
      order = response.data;
      handoverDialog.close();
      renderOrder();
    } catch (failure) {
      error.textContent = failure.message;
      error.hidden = false;
    }
  };

  api(`orders/${encodeURIComponent(orderId)}`)
    .then(response => { order = response.data; renderOrder(); })
    .catch(error => showDetailError(error.message));
})();
