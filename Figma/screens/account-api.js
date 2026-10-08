window.SaeyAccountApi = {
  async request(path, method, body = null) {
    const token = localStorage.getItem('saey_api_token');
    if (!token) throw new Error('انتهت جلسة الدخول. سجل الدخول ثم حاول مرة أخرى.');

    const response = await fetch(`http://127.0.0.1:8000/api${path}`, {
      method,
      headers: {
        Accept: 'application/json',
        Authorization: `Bearer ${token}`,
        ...(body ? { 'Content-Type': 'application/json' } : {})
      },
      ...(body ? { body: JSON.stringify(body) } : {})
    });

    let result;
    try {
      result = await response.json();
    } catch {
      throw new Error('تعذر قراءة رد الخادم. حاول مرة أخرى.');
    }

    if (!response.ok) throw new Error(result.message || 'تعذر إتمام الطلب.');
    return result;
  },

  clearToken() {
    localStorage.removeItem('saey_api_token');
  }
};
