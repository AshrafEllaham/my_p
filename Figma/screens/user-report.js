(() => {
  const origin = location.protocol === 'file:' ? 'http://127.0.0.1:8000' :
    ['localhost', '127.0.0.1'].includes(location.hostname) ? `${location.protocol}//${location.hostname}:8000` : location.origin;
  const token = () => localStorage.getItem('saey_api_token');

  window.SaeyUserReport = {
    bind({ type, itemPath, getItemId }) {
      const dialog = document.getElementById('reportDialog');
      const form = document.getElementById('reportForm');
      const reasons = document.getElementById('reportReasons');
      const status = document.getElementById('reportStatus');
      const submit = document.getElementById('submitReport');
      const trigger = document.getElementById('report');
      const close = document.getElementById('closeReport');
      if (!dialog || !form || !reasons || !status || !submit || !trigger || !close) return;

      close.addEventListener('click', () => dialog.close());
      trigger.addEventListener('click', async () => {
        if (!getItemId()) return;
        dialog.showModal();
        reasons.replaceChildren();
        submit.disabled = true;
        status.textContent = 'جارٍ تحميل أسباب الإبلاغ...';
        try {
          const response = await fetch(`${origin}/api/user/report-reasons?type=${encodeURIComponent(type)}`, {
            headers: { Accept: 'application/json', 'Accept-Language': 'ar', ...(token() ? { Authorization: `Bearer ${token()}` } : {}) }
          });
          const payload = await response.json();
          if (!response.ok) throw new Error(payload.message || 'تعذر تحميل أسباب الإبلاغ.');
          (payload.data || []).forEach(reason => {
            const label = document.createElement('label');
            const radio = document.createElement('input');
            radio.type = 'radio';
            radio.name = 'report_reason_id';
            radio.value = reason.id;
            radio.required = true;
            label.append(radio, document.createTextNode(` ${reason.title || ''}`));
            reasons.append(label);
          });
          status.textContent = reasons.childElementCount ? '' : 'لا توجد أسباب إبلاغ متاحة حاليًا.';
        } catch (error) {
          status.textContent = error.message || 'تعذر تحميل أسباب الإبلاغ.';
        }
      });
      reasons.addEventListener('change', () => { submit.disabled = false; status.textContent = ''; });
      form.addEventListener('submit', async event => {
        event.preventDefault();
        const reason = form.querySelector('input[name="report_reason_id"]:checked');
        const itemId = getItemId();
        if (!reason || !itemId) return;
        submit.disabled = true;
        status.textContent = 'جارٍ إرسال البلاغ...';
        try {
          const response = await fetch(`${origin}/api/user/${itemPath}/${encodeURIComponent(itemId)}/reports`, {
            method: 'POST',
            headers: { Accept: 'application/json', 'Accept-Language': 'ar', 'Content-Type': 'application/json', ...(token() ? { Authorization: `Bearer ${token()}` } : {}) },
            body: JSON.stringify({ report_reason_id: Number(reason.value) })
          });
          const payload = await response.json();
          if (!response.ok) throw new Error(payload.message || 'تعذر إرسال البلاغ.');
          status.textContent = payload.message || 'تم إرسال البلاغ بنجاح.';
        } catch (error) {
          status.textContent = error.message || 'تعذر إرسال البلاغ.';
          submit.disabled = false;
        }
      });
    }
  };
})();
