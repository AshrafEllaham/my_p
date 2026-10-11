(function () {
  const isLocalPreviewHost = ['localhost', '127.0.0.1'].includes(window.location.hostname);
  const apiOrigin = window.location.protocol === 'file:'
    ? 'http://localhost:8000'
    : isLocalPreviewHost && window.location.port !== '8000'
      ? `${window.location.protocol}//${window.location.hostname}:8000`
      : window.location.origin;
  const storedBaseUrl = localStorage.getItem('saey_api_base');
  const baseUrl = new URL(storedBaseUrl || '/api/store', apiOrigin).toString().replace(/\/$/, '');
  const token = localStorage.getItem('saey_api_token') || '';

  async function request(path, options = {}, includeEnvelope = false) {
    let response;
    try {
      response = await fetch(`${baseUrl}${path}`, {
        ...options,
        headers: {
          Accept: 'application/json',
          'Accept-Language': document.documentElement.lang || 'ar',
          'Content-Type': 'application/json',
          ...(token ? { Authorization: `Bearer ${token}` } : {}),
          ...(options.headers || {}),
        },
      });
    } catch (error) {
      const language = document.documentElement.lang || 'ar';
      throw new Error(language === 'ar' ? 'تعذر الاتصال بالخادم. تحقق من الاتصال ثم أعد المحاولة.' : 'Could not connect to the server. Check your connection and try again.');
    }
    let result = {};
    try { result = await response.json(); } catch (error) { /* Keep the HTTP error below useful. */ }
    if (!response.ok || result.code >= 400) {
      const errors = Object.values(result.errors || {}).flat();
      const language = document.documentElement.lang || 'ar';
      const unauthorizedMessage = language === 'ar'
        ? 'سجّل الدخول إلى حساب المتجر لعرض الكوبونات.'
        : 'Sign in to your store account to view coupons.';
      const error = new Error(response.status === 401 ? unauthorizedMessage : errors[0] || result.message || `تعذر تنفيذ الطلب (${response.status}).`);
      error.status = response.status;
      throw error;
    }
    return includeEnvelope ? result : result.data;
  }

  function fromApi(item) {
    return {
      id: String(item.id),
      code: item.code,
      type: item.discount_type === 'percentage' ? 'percent' : 'fixed',
      value: Number(item.discount_value),
      minOrder: Number(item.minimum_order_amount || 0),
      maximumDiscount: item.maximum_discount_amount == null ? null : Number(item.maximum_discount_amount),
      expiresAt: item.expires_at ? item.expires_at.slice(0, 10) : '',
      startsAt: item.starts_at ? item.starts_at.slice(0, 10) : '',
      maxUses: Number(item.max_uses || 0),
      perUserLimit: Number(item.max_uses_per_user || 1),
      enabled: item.is_active,
      uses: Number(item.uses_count || 0),
      status: item.status,
      statistics: item.statistics ? {
        completedOrders: Number(item.statistics.completed_orders_count || 0),
        sales: Number(item.statistics.sales_amount || 0),
        discount: Number(item.statistics.discount_amount || 0),
        averageOrder: Number(item.statistics.average_order_amount || 0),
        usageLast7Days: (item.statistics.usage_last_7_days || []).map(day => ({ date: day.date, count: Number(day.count || 0) })),
      } : null,
    };
  }

  function toApi(item) {
    return {
      code: item.code,
      discount_type: item.type === 'percent' ? 'percentage' : 'fixed',
      discount_value: item.value,
      minimum_order_amount: item.minOrder,
      max_uses: item.maxUses,
      max_uses_per_user: item.perUserLimit,
      expires_at: `${item.expiresAt}T23:59:59`,
      is_active: item.enabled,
      ...(Object.hasOwn(item, 'maximumDiscount') ? { maximum_discount_amount: item.maximumDiscount } : {}),
      ...(Object.hasOwn(item, 'startsAt') ? { starts_at: item.startsAt ? `${item.startsAt}T00:00:00` : null } : {}),
    };
  }

  window.SaeyCouponsApi = {
    async listWithSummary() {
      const result = await request('/coupons?pagination=off', {}, true);
      return { items: (result.data || []).map(fromApi), summary: result.summary || {} };
    },
    async list(status) {
      const params = new URLSearchParams({ pagination: 'off' });
      if (status) params.set('status', status);
      return (await request(`/coupons?${params}`)).map(fromApi);
    },
    async find(id) { return fromApi(await request(`/coupons/${encodeURIComponent(id)}`)); },
    async create(item) { return fromApi(await request('/coupons', { method: 'POST', body: JSON.stringify(toApi(item)) })); },
    async update(id, item) { return fromApi(await request(`/coupons/${encodeURIComponent(id)}`, { method: 'PATCH', body: JSON.stringify(toApi(item)) })); },
    async toggle(id) { return fromApi(await request(`/coupons/${encodeURIComponent(id)}/toggle`, { method: 'PATCH' })); },
    async remove(id) { await request(`/coupons/${encodeURIComponent(id)}`, { method: 'DELETE' }); },
  };
})();
