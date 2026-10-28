(() => {
  const token = localStorage.getItem('saey_api_token');
  const params = new URLSearchParams(location.search);
  const orderId = params.get('order_id') || (params.get('design_mode') === 'static' ? '601' : null);
  if (!token || !orderId) return;
  const origin = location.protocol === 'file:' ? 'http://127.0.0.1:8000' :
    ['localhost', '127.0.0.1'].includes(location.hostname) ? `${location.protocol}//${location.hostname}:8000` : location.origin;
  const statusLabel = {
    open: 'وصل البلاغ',
    information_requested: 'بانتظار ردك',
    under_review: 'قيد المراجعة',
    order_completed: 'تم إتمام الطلب',
    refunded: 'تم رد المبلغ',
    rejected: 'تم رفض البلاغ'
  };
  const status = document.getElementById('disputeStatus');
  const note = document.getElementById('disputeNote');
  const card = document.getElementById('responseCard');
  const error = document.getElementById('responseError');
  const send = document.getElementById('sendResponse');
  const details = document.createElement('div');
  details.id = 'disputeMessages';
  details.className = 'dispute-messages';
  card.before(details);

  const api = async (path, method = 'GET', body = null) => {
    const response = await fetch(`${origin}/api/user/${path}`, {
      method,
      headers: {
        Accept: 'application/json',
        'Accept-Language': 'ar',
        Authorization: `Bearer ${token}`
      },
      ...(body ? { body } : {})
    });
    const payload = await response.json();
    if (!response.ok) throw new Error(Object.values(payload.errors || {}).flat()[0] || payload.message || 'تعذر تحميل تفاصيل المشكلة.');
    return payload.data || {};
  };
  const render = dispute => {
    status.textContent = statusLabel[dispute.status] || 'قيد المراجعة';
    note.textContent = `${dispute.reason || 'مشكلة في الطلب'}: ${dispute.details || ''}`;
    document.getElementById('orderCode').textContent = `#${params.get('code') || (params.get('design_mode') === 'static' ? 'SA-1014' : '—')}`;
    details.replaceChildren();
    (dispute.messages || []).forEach(message => {
      const item = document.createElement('p');
      item.className = 'dispute-note';
      item.textContent = message.body || '';
      details.append(item);
    });
    const awaiting = dispute.status === 'information_requested';
    card.hidden = !awaiting;
    const decision = document.getElementById('merchantDecision');
    if (['order_completed', 'refunded', 'rejected'].includes(dispute.status)) {
      decision.hidden = false;
      document.getElementById('decisionNote').textContent = dispute.resolution_note || statusLabel[dispute.status];
    }
    document.getElementById('backLink').href = `21-order-details.html?order_id=${encodeURIComponent(orderId)}&code=${encodeURIComponent(params.get('code') || '')}`;
  };
  const load = async () => {
    try {
      render(await api(`orders/${encodeURIComponent(orderId)}/disputes`));
    } catch (failure) {
      note.textContent = failure.message;
      status.textContent = 'تعذر تحميل الحالة';
    }
  };
  send.onclick = async () => {
    const bodyText = document.getElementById('customerResponse').value.trim();
    if (bodyText.length < 10) {
      error.textContent = 'اكتب تفاصيل لا تقل عن ١٠ أحرف.';
      error.hidden = false;
      return;
    }
    send.disabled = true;
    error.hidden = true;
    const body = new FormData();
    body.append('body', bodyText);
    try {
      render(await api(`orders/${encodeURIComponent(orderId)}/disputes/messages`, 'POST', body));
    } catch (failure) {
      error.textContent = failure.message;
      error.hidden = false;
    } finally {
      send.disabled = false;
    }
  };
  load();
})();
