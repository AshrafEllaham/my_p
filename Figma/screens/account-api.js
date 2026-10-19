const accountApiOrigin = location.protocol === 'file:' ? 'http://127.0.0.1:8000' :
  ['localhost', '127.0.0.1'].includes(location.hostname) ? `${location.protocol}//${location.hostname}:8000` : location.origin;

window.SaeyAccountApi = {
  origin: accountApiOrigin,
  async request(path, method, body = null) {
    const token = localStorage.getItem('saey_api_token');
    if (!token) throw new Error('انتهت جلسة الدخول. سجل الدخول ثم حاول مرة أخرى.');

    const isFormData = typeof FormData !== 'undefined' && body instanceof FormData;
    const response = await fetch(`${accountApiOrigin}/api${path}`, {
      method,
      headers: {
        Accept: 'application/json',
        'Accept-Language': 'ar',
        Authorization: `Bearer ${token}`,
        ...(body && !isFormData ? { 'Content-Type': 'application/json' } : {})
      },
      ...(body ? { body: isFormData ? body : JSON.stringify(body) } : {})
    });

    let result;
    try {
      result = await response.json();
    } catch {
      throw new Error('تعذر قراءة رد الخادم. حاول مرة أخرى.');
    }

    if (!response.ok) {
      const firstError = Object.values(result.errors || {}).flat()[0];
      throw new Error(firstError || result.message || 'تعذر إتمام الطلب.');
    }
    return result;
  },

  clearToken() {
    localStorage.removeItem('saey_api_token');
  }
};
